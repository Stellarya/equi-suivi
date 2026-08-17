<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\AiUsage;
use App\Exception\QuotaExceededException;
use App\Repository\AiUsageRepository;
use Doctrine\ORM\EntityManagerInterface;

final class AiQuotaGuard
{
    public function __construct(
        private readonly AiUsageRepository $aiUsageRepository,
        private readonly EntityManagerInterface $em,
        private readonly int $aiDailyCallLimit,
    )
    {}

    public function assertCanCall(): void {
        if ($this->getTodayUsage()->getCallCount() >= $this->aiDailyCallLimit) {
            throw new QuotaExceededException(
                sprintf('Plafond quotidient de %d appels atteint.', $this->aiDailyCallLimit)
            );
        }
    }

    public function recordCall(): void
    {
        $usage = $this->getTodayUsage();
        $usage->setCallCount($usage->getCallCount() + 1);

        $this->em->persist($usage);
        $this->em->flush();
    }

    private function getTodayUsage(): AiUsage
    {
        $today = new \DateTimeImmutable('today');

        $usage = $this->aiUsageRepository->findOneBy(['usageDate' => $today]);

        if($usage === null) {
            $usage = new AiUsage;
            $usage->setUsageDate($today);
        }

        return $usage;
    }
}