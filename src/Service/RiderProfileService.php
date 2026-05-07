<?php

namespace App\Service;

use App\Entity\Rider;
use App\Entity\RiderGalop;

class RiderProfileService {

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
}