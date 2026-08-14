<?php

namespace App\Service;

use App\Entity\AppUser;
use App\Entity\Competition;
use App\Entity\CompetitionRegistration;
use App\Entity\Rider;
use App\Entity\RiderGalop;
use DateTimeImmutable;
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

    public function getCompetitionsRegistrations(Rider $rider): array {
        $registrationsHistory = [];
        $registrationsToGo = [];

        $today = new DateTimeImmutable('today');

        foreach ($rider->getCompetitionRegistrations() as $registration) {
            $competition = $registration->getCompetition();

            if($competition === null) {
                continue;
            }

            if ($competition->getEndDate() < $today) {
                $registrationsHistory[] = $registration;
            } else {
                $registrationsToGo[] = $registration;
            }
        }
        return [
            'registrationsToGo' => $this->sortCompetitionRegistrations($registrationsToGo),
            'registrationsHistory' => $this->sortCompetitionRegistrations($registrationsHistory, false)
        ];
    }

    /**
     * 
     */
    private function sortCompetitionRegistrations(
        array $registrations,
        bool $ascending = true
    ): array {
        usort(
            $registrations,
            static function (
                CompetitionRegistration $firstRegistration,
                CompetitionRegistration $secondRegistration
            ) use ($ascending): int {
                $comparison =
                    $firstRegistration->getCompetition()->getStartDate()
                    <=>
                    $secondRegistration->getCompetition()->getStartDate();

                return $ascending
                    ? $comparison
                    : -$comparison;
            }
        );

        return $registrations;
    }

    public function buildProfileViewData(?Rider $rider): array {
        if($rider === null) {
            return [
                'rider' => null,
                'lastGalop' => null,
                'galopHistory' => [],
                'registrationsHistory' => [],
                'registrationsToGo' => [],
            ];
        }

        $galopHistory = $this->getSortedGalopHistory($rider);
        $registrations = $this->getCompetitionsRegistrations($rider);

        return [
            'rider' => $rider,
            'lastGalop' => $galopHistory[0] ?? null,
            'galopHistory' => $galopHistory,
            'registrationsHistory' => $registrations['registrationsHistory'],
            'registrationsToGo' => $registrations['registrationsToGo']
        ];
    }

    public function saveProfile(): void {
        $this->entityManager->flush();
    }    
}