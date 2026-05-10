<?php

namespace App\Repository;

use App\Entity\AppUser;
use App\Entity\Horse;
use App\Entity\Rider;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Horse>
 */
class HorseRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Horse::class);
    }

    public function findForRider(Rider $rider, bool $activeOnly = true): array
    {
        $queryBuilder = $this->createQueryBuilder('horse')
            ->innerJoin('horse.riders', 'rider')
            ->andWhere('rider = :rider')
            ->setParameter('rider', $rider)
            ->orderBy('horse.name', 'ASC');
              
        if($activeOnly) {
            $queryBuilder
                ->andWhere('horse.status = :activeStatus')
                ->setParameter('activeStatus', Horse::STATUS_ACTIVE);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult();
    }

    public function findForOwner(AppUser $owner, bool $activeOnly = true): array
    {
        $queryBuilder = $this->createQueryBuilder('horse')
            ->andWhere('horse.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('horse.name', 'ASC');
        
        if ($activeOnly) {
            $queryBuilder
                ->andWhere('horse.status = :activeStatus')
                ->setParameter('activeStatus', Horse::STATUS_ACTIVE);
        }
            
        return $queryBuilder
            ->getQuery()
            ->getResult();
    }
}
