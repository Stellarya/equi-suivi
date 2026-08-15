<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\CompetitionRegistration;
use App\Entity\Rider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Competition>
 */
class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    public function findByRider(Rider $rider): array
    {
        return $this->createQueryBuilder('competition')
            ->innerJoin(
                CompetitionRegistration::class,
                'registration',
                'WITH',
                'registration.competition = competition'
            )
            ->andWhere('registration.rider = :rider')
            ->setParameter('rider', $rider)
            ->distinct()
            ->orderBy('competition.startDate', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
