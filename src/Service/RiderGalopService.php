<?php

namespace App\Service;

use App\Entity\Rider;
use App\Entity\RiderGalop;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RiderGalopService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public function createForRider(Rider $rider): RiderGalop
    {
        $riderGalop = new RiderGalop();
        $riderGalop->setRider($rider);

        return $riderGalop;
    }

    public function assertBelongsToRider(RiderGalop $riderGalop, Rider $rider): void
    {
        if ($riderGalop->getRider() !== $rider) {
            throw new AccessDeniedHttpException('Ce galop ne peut pas être modifié par cet utilisateur.');
        }
    }

    public function save(RiderGalop $riderGalop): void
    {
        $this->entityManager->persist($riderGalop);
        $this->entityManager->flush();
    }
}