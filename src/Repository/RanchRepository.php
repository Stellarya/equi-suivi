<?php

namespace App\Repository;

use App\Entity\Ranch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Region;

/**
 * @extends ServiceEntityRepository<Ranch>
 */
class RanchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ranch::class);
    }
}
