<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\Competition;
use App\Entity\StatusCompetition;
use App\Repository\CompetitionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CompetitionService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CompetitionRepository $competitionRepository
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