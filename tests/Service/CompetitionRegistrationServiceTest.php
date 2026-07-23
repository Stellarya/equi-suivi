<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\Competition;
use App\Entity\CompetitionRegistration;
use App\Service\CompetitionRegistrationService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class CompetitionRegistrationServiceTest extends TestCase {
    public function testRegisterCoupleAssociatesAndPersistsRegistration(): void {
        $competition = $this->createMock(Competition::class);
        $registration = $this->createMock(CompetitionRegistration::class);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $registration
            ->expects(self::once())
            ->method('setCompetition')
            ->with(self::identicalTo($competition));
        
        $registration
            ->expects(self::once())
            ->method('setRegistrationDate')
            ->with(
                self::callback(
                    static function (mixed $date): bool {
                        self::assertInstanceOf(DateTime::class, $date);

                        $difference = abs(
                            time() - $date->getTimestamp(),
                        );

                        self::assertLessThanOrEqual(2, $difference);

                        return true;
                    }
                )
            );
        
        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with(self::identicalTo($registration));
        
        $entityManager
            ->expects(self::once())
            ->method('flush');
        
        $service = new CompetitionRegistrationService($entityManager);

        $service->registerCouple($registration, $competition);
    }
}