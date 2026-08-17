<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\AiUsage;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry as PersistenceManagerRegistry;

/**
 * @extends ServiceEntityRepository<AiUsage>
 */
class AiUsageRepository extends ServiceEntityRepository
{
    public function __construct(PersistenceManagerRegistry $registry)
    {
        parent::__construct($registry, AiUsage::class);
    }
}