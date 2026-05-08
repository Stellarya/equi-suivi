<?php

namespace App\Tests\Service;

use App\Entity\Rider;
use App\Entity\RiderGalop;
use App\Service\RiderGalopService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class RiderGalopServiceTest extends TestCase
{
    public function testCreateForRiderCreatesRiderGalopLinkedToRider(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $service = new RiderGalopService($entityManager);

        $rider = new Rider();
        $rider->setFirstName('Lily');
        $rider->setLastName('Test');

        $riderGalop = $service->createForRider($rider);

        self::assertInstanceOf(RiderGalop::class, $riderGalop);
        self::assertSame($rider, $riderGalop->getRider());
    }

    public function testSavePersistsAndFlushesRiderGalop(): void
    {
        $riderGalop = new RiderGalop();

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($riderGalop);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = new RiderGalopService($entityManager);

        $service->save($riderGalop);
    }
}