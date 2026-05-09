<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\Horse;
use App\Repository\HorseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class HorseService
{

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly HorseRepository $horseRepository
    ){}

    /**
     * @return Horse[]
     */
    public function getVisibleHorsesForUser(AppUser $user): array
    {
        $rider = $user->getRider();

        if($rider !== null) {
            return $this->horseRepository->findForRider($rider);
        }

        return $this->horseRepository->findForOwner($user);
    }

    public function createForUser(AppUser $user): Horse
    {
        $horse = new Horse;
        $horse->setOwner($user);
        $horse->setStatus(Horse::STATUS_ACTIVE);

        if($user->getRider() !== null) {
            $horse->addRider($user->getRider());
        }

        return $horse;
    }

    public function assertCanManageHorse(Horse $horse, AppUser $user): void
    {
        if($horse->getOwner() === $user) {
            return;
        }

        if($user->getRider() !== null && $horse->getRiders() === $user->getRider()) {
            return;
        }

        throw new AccessDeniedHttpException('Vous ne pouvez pas gérer ce cheval.');
    }

    public function save(Horse $horse): void
    {
        $this->entityManager->persist($horse);
        $this->entityManager->flush();
    }

    public function setResting(Horse $horse): void
    {
        $horse->setStatus(Horse::STATUS_RESTING);

        $this->entityManager->flush();
    }

    public function retire(Horse $horse): void
    {
        $horse->setStatus(Horse::STATUS_RETIRED);

        $this->entityManager->flush();
    }

    public function delete(Horse $horse): void
    {
        $this->entityManager->remove($horse);
        $this->entityManager->flush();
    }
}