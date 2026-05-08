<?php

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\Rider;
use App\Entity\RiderGalop;
use App\Service\RiderProfileService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class RiderProfileServiceTest extends TestCase
{
    public function testGetRiderForUserReturnsRider(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $user = new AppUser();

        $rider = new Rider();
        $rider->setFirstName('Marie');
        $rider->setLastName('Test');
        $rider->setAppUser($user);

        $user->setRider($rider);

        self::assertSame($rider, $service->getRiderForUser($user));
    }

    public function testGetRiderForUserThrowsExceptionWhenUserHasNoRider(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $user = new AppUser();

        $this->expectException(NotFoundHttpException::class);

        $service->getRiderForUser($user);
    }

    public function testGetSortedGalopHistorySortsByObtainedYearDescending(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $rider = new Rider();

        $riderGalop2020 = new RiderGalop();
        $riderGalop2020->setObtainedYear(2020);

        $riderGalop2024 = new RiderGalop();
        $riderGalop2024->setObtainedYear(2024);

        $riderGalopWithoutYear = new RiderGalop();
        $riderGalopWithoutYear->setObtainedYear(null);

        $rider->addGalopHistory($riderGalop2020);
        $rider->addGalopHistory($riderGalopWithoutYear);
        $rider->addGalopHistory($riderGalop2024);

        $sortedGalopHistory = $service->getSortedGalopHistory($rider);

        self::assertSame($riderGalop2024, $sortedGalopHistory[0]);
        self::assertSame($riderGalop2020, $sortedGalopHistory[1]);
        self::assertSame($riderGalopWithoutYear, $sortedGalopHistory[2]);
    }

    public function testGetLastGalopReturnsMostRecentGalop(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $rider = new Rider();

        $oldGalop = new RiderGalop();
        $oldGalop->setObtainedYear(2018);

        $lastGalop = new RiderGalop();
        $lastGalop->setObtainedYear(2023);

        $rider->addGalopHistory($oldGalop);
        $rider->addGalopHistory($lastGalop);

        self::assertSame($lastGalop, $service->getLastGalop($rider));
    }

    public function testGetLastGalopReturnsNullWhenHistoryIsEmpty(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $rider = new Rider();

        self::assertNull($service->getLastGalop($rider));
    }

    public function testBuildProfileViewDataReturnsEmptyDataWhenRiderIsNull(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $viewData = $service->buildProfileViewData(null);

        self::assertSame([
            'rider' => null,
            'lastGalop' => null,
            'galopHistory' => [],
        ], $viewData);
    }

    public function testBuildProfileViewDataReturnsRiderData(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $service = new RiderProfileService($entityManager);

        $rider = new Rider();

        $oldGalop = new RiderGalop();
        $oldGalop->setObtainedYear(2019);

        $lastGalop = new RiderGalop();
        $lastGalop->setObtainedYear(2024);

        $rider->addGalopHistory($oldGalop);
        $rider->addGalopHistory($lastGalop);

        $viewData = $service->buildProfileViewData($rider);

        self::assertSame($rider, $viewData['rider']);
        self::assertSame($lastGalop, $viewData['lastGalop']);
        self::assertSame([$lastGalop, $oldGalop], $viewData['galopHistory']);
    }

    public function testSaveProfileFlushesEntityManager(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new RiderProfileService($entityManager);

        $service->saveProfile();
    }
}