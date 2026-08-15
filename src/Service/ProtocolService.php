<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\CompetitionEntry;
use App\Entity\Protocol;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;

final class ProtocolService {

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly SluggerInterface $slugger,
        private readonly string $protocolFilesDirectory,
    )
    {}

    public function assertCanManageProtocol(CompetitionEntry $entry, AppUser $user): void {
        $registration = $entry->getCompetitionRegistration();
        $rider = $user->getRider();

        if($rider !== null && $registration->getRider() === $rider) {
            return;
        }

        if($registration->getHorse()?->getOwner() === $user) {
            return;
        }

        if ($registration === null) {
            throw new AccessDeniedHttpException("vous n'avez pas accès à cette participation");
        }
        
    }

    public function createFromUpload(CompetitionEntry $entry, UploadedFile $file, string $judgePosition): Protocol {

        foreach($entry->getProtocols() as $existing) {
            if ($existing->getJudgePosition() === $judgePosition) {
                throw new \DomainException(
                    sprintf('Un protocole du juge en %s existe dejà pour cette épreuve.', $judgePosition)
                );
            }
        }
        $originalFileName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);

        $newFileName = sprintf(
            '%s-%s.%s',
            $this->slugger->slug($originalFileName),
            uniqid(),
            $file->guessExtension()
        );

        $file->move($this->protocolFilesDirectory, $newFileName);

        $protocol = new Protocol();
        $protocol->setCompetitionEntry($entry);
        $protocol->setFilePath($newFileName);
        $protocol->setJudgePosition($judgePosition);
        $protocol->setStatus(Protocol::STATUS_UPLOADED);

        $this->em->persist($protocol);
        $this->em->flush();

        return $protocol;
    }

    public function getAbsolutePath(Protocol $protocol): string
    {
        return sprintf('%s/%s', $this->protocolFilesDirectory, $protocol->getFilePath());
    }
}