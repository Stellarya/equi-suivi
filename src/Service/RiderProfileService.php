<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\Rider;
use App\Entity\RiderGalop;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RiderProfileService {

    public function __construct(
        private readonly EntityManagerInterface $entityManager
    ) {}

    public function getRiderForUser(AppUser $user): Rider {
        $rider = $user->getRider();

        if($rider === null) {
            throw new NotFoundHttpException('Aucun profil cavalier n\'est rattaché à ce compte');
        }

        return $rider;
    }

    /**
     * @return RiderGalop[]
     */
    public function getSortedGalopHistory(Rider $rider): array {
        $galopHistory = $rider->getGalopHistory()->toArray();

        usort($galopHistory, static function (RiderGalop $firstGalop, RiderGalop $secondGalop): int {
            return ($secondGalop->getObtainedYear() ?? 0) <=> ($firstGalop->getObtainedYear() ?? 0);
        });

        return $galopHistory;
    }

    public function getLastGalop(Rider $rider): ?RiderGalop {
        $galopHistory = $this->getSortedGalopHistory($rider);

        return $galopHistory[0] ?? null;
    }

    public function buildProfileViewData(?Rider $rider): array {
        if($rider === null) {
            return [
                'rider' => null,
                'lastGalop' => null,
                'galopHistory' => []
            ];
        }

        $galopHistory = $this->getSortedGalopHistory($rider);

        return [
            'rider' => $rider,
            'lastGalop' => $galopHistory[0] ?? null,
            'galopHistory' => $galopHistory
        ];
    }

    public function saveProfile(): void {
        $this->entityManager->flush();
    }    
}