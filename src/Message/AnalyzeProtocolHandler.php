<?php

namespace App\Message;

use App\Entity\Protocol;
use App\Exception\ProtocolAnalysisException;
use App\Exception\QuotaExceededException;
use App\Repository\ProtocolRepository;
use App\Service\AiQuotaGuard;
use App\Service\ProtocolAnalysisApplier;
use App\Service\ProtocolAnalyzerInterface;
use App\Service\ProtocolService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Exception\UnrecoverableMessageHandlingException;

#[AsMessageHandler()]
final class AnalyzeProtocolHandler
{
    public function __construct(
        private readonly ProtocolRepository $protocolRepository,
        private readonly ProtocolService $protocolService,
        private readonly ProtocolAnalyzerInterface $analyzer,
        private readonly AiQuotaGuard $quotaGuard,
        private readonly ProtocolAnalysisApplier $applier,
        private readonly EntityManagerInterface $em,
    )
    {}

    public function __invoke(AnalyzeProtocolMessage $message): void
    {
        $protocol = $this->protocolRepository->find($message->protocolId);

        if ($protocol === null){
            return;
        }

        try {
           $this->quotaGuard->assertCancall();
        } catch (QuotaExceededException $e) {
           $protocol->setStatus(Protocol::STATUS_FAILED);
           $this->em->flush();

           throw new UnrecoverableMessageHandlingException(
            'Quota IA atteint, analyse abandonnée',
            previous: $e
           );
        }

        $protocol->setStatus(Protocol::STATUS_ANALYZING);
        $this->em->flush();

       $dressageTest = $protocol->getCompetitionEntry()?->getDressageTest();

        if ($dressageTest === null) {
            $protocol->setStatus(Protocol::STATUS_FAILED);
            $this->em->flush();

            throw new UnrecoverableMessageHandlingException('Reprise introuvable pour ce protocole.');
        }

        $judgePosition = $protocol->getJudgePosition();

        if ($judgePosition === null) {
            $protocol->setStatus(Protocol::STATUS_FAILED);
            $this->em->flush();

            throw new UnrecoverableMessageHandlingException('Position du juge manquante.');
        }

        $expectedFigureNumbers = [];
        $activeFigures = [];
        foreach ($dressageTest->getProtocolFigures() as $figure) {
            if ($figure->isEstActif()) {
                $expectedFigureNumbers[] = $figure->getNumber();
                $activeFigures[] = $figure;
            }
        }

        $this->quotaGuard->recordCall();

        $result = $this->analyzer->analyze(
            $this->protocolService->getAbsolutePath($protocol),
            $expectedFigureNumbers,
            $judgePosition
        );

        try {
            $complete = $this->applier->apply($protocol, $result, $activeFigures);
        } catch (\Throwable $e) {
            if ($this->em->isOpen()) {
                $protocol->setStatus(Protocol::STATUS_FAILED);
                $this->em->flush();
            }

            throw new UnrecoverableMessageHandlingException($e->getMessage(), previous: $e);
        }

        $protocol->setStatus($complete ? Protocol::STATUS_ANALYZED : Protocol::STATUS_NEEDS_REVIEW);
        $this->em->flush();
    }

}