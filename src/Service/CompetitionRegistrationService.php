<?php

namespace App\Service;

use App\Entity\Competition;
use App\Entity\CompetitionRegistration;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;

class CompetitionRegistrationService {

    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
      $this->em = $em;  
    }

    public function registerCouple(CompetitionRegistration $registration, Competition $competition): void {
        $registration->setCompetition($competition);
        $registration->setRegistrationDate(new DateTime());

        $this->em->persist($registration);
        $this->em->flush();
    }
}