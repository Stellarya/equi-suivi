<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\Competition;
use App\Entity\StatusCompetition;
use App\Repository\CompetitionRepository;
use App\Repository\RiderRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompetitionService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompetitionRepository $competitionRepository,
        private readonly RiderRepository $riderRepository
    ){}

    /**
     * Create or edit a competition  
     */
    public function saveCompetition(Competition $competition): void {
        if($competition->getStatusCompetition() === null) {
            $statusCompetition = new StatusCompetition();
            $statusCompetition->setMnemonique('EN_ATTENTE');
            $competition->setStatusCompetition($statusCompetition);
        }

        $this->entityManager->persist($competition);
        $this->entityManager->flush();
    }

    public function getCompetitionsForUser(AppUser $user): array {
        $rider = $this->riderRepository->findOneBy([
            'appUser' => $user,
        ]);

        if (!$rider) {
            return [];
        }

        return $this->competitionRepository->findByRider($rider);

    }

    /**
     * Get all competitions sorted by starDate
     * 
     * @return Competition[]
     */
    public function getAllCompetitions(): array
    {
        return $this->competitionRepository->findBy([], ['startDate' => 'DESC']);
    }
}