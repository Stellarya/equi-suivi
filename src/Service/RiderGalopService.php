<?php

namespace App\Service;

use App\Entity\Rider;
use App\Entity\RiderGalop;
use Doctrine\ORM\EntityManagerInterface;

class RiderGalopService {

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    )
    {}

    public function createForRider(Rider $rider): RiderGalop {
        $riderGalop = new RiderGalop();
        $riderGalop->setRider($rider);

        return $riderGalop;
    }

    public function save(RiderGalop $riderGalop): void {
        $this->entityManager->persist($riderGalop);
        $this->entityManager->flush();
    }
}