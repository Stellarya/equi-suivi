<?php

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\Horse;
use App\Entity\Rider;
use App\Repository\HorseRepository;
use App\Service\HorseService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\String\Slugger\SluggerInterface;
use Twig\Node\Expression\Test\TrueTest;

final class HorseServiceTest extends TestCase
{
    public function testCreateForUserCreatesActiveHorseWithOwner(): void
    {
        $service = $this->createService();

        $user = new AppUser();

        $horse = $service->createForUser($user);

        self::assertInstanceOf(Horse::class, $horse);
        self::assertSame($user, $horse->getOwner());
        self::assertSame(Horse::STATUS_ACTIVE, $horse->getStatus());
    }

    public function testCreateForUserAddsUserRiderWhenUserHasRider(): void
    {
        $service = $this->createService();

        $user = new AppUser();
        $rider = $this->createRiderForUser($user);

        $horse = $service->createForUser($user);

        self::assertTrue($horse->getRiders()->contains($rider));
    }

    public function testGetVisibleHorsesForUserUsesRiderWhenUserHasRider(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $slugger = $this->createMock(SluggerInterface::class);
        $horseRepository = $this->createMock(HorseRepository::class);

        $user = new AppUser();
        $rider = $this->createRiderForUser($user);

        $expectedHorses = [
            new Horse(),
        ];

        $horseRepository
            ->expects(self::once())
            ->method('findForRider')
            ->with($rider, true)
            ->willReturn($expectedHorses);

        $horseRepository
            ->expects(self::never())
            ->method('findForOwner');

        $service = new HorseService(
            entityManager: $entityManager,
            horseRepository: $horseRepository,
            slugger: $slugger,
            horsePhotosDirectory: '/tmp'
        );

        self::assertSame($expectedHorses, $service->getVisibleHorsesForUser($user));
    }

    public function testGetVisibleHorsesForUserUsesOwnerWhenUserHasNoRider(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $slugger = $this->createMock(SluggerInterface::class);
        $horseRepository = $this->createMock(HorseRepository::class);

        $user = new AppUser();

        $expectedHorses = [
            new Horse(),
        ];

        $horseRepository
            ->expects(self::never())
            ->method('findForRider');

        $horseRepository
            ->expects(self::once())
            ->method('findForOwner')
            ->with($user, true)
            ->willReturn($expectedHorses);

        $service = new HorseService(
            entityManager: $entityManager,
            horseRepository: $horseRepository,
            slugger: $slugger,
            horsePhotosDirectory: '/tmp'
        );

        self::assertSame($expectedHorses, $service->getVisibleHorsesForUser($user));
    }

    public function testAssertCanViewHorseAllowsOwner(): void
    {
        $service = $this->createService();

        $owner = new AppUser();

        $horse = new Horse();
        $horse->setOwner($owner);

        $service->assertCanViewHorse($horse, $owner);

        self::assertTrue(true);
    }

    public function testAssertCanViewHorseAllowsAttachedRider(): void
    {
        $service = $this->createService();

        $owner = new AppUser();
        $user = new AppUser();
        $rider = $this->createRiderForUser($user);

        $horse = new Horse();
        $horse->setOwner($owner);
        $horse->addRider($rider);

        $service->assertCanViewHorse($horse, $user);

        self::assertTrue(true);
    }

    public function testAssertCanViewHorseThrowsExceptionForUnrelatedUser(): void
    {
        $service = $this->createService();

        $owner = new AppUser();
        $unrelatedUser = new AppUser();

        $horse = new Horse();
        $horse->setOwner($owner);

        $this->expectException(AccessDeniedHttpException::class);

        $service->assertCanViewHorse($horse, $unrelatedUser);
    }

    public function testAssertCanEditHorseAllowsOwner(): void
    {
        $service = $this->createService();

        $owner = new AppUser();

        $horse = new Horse();
        $horse->setOwner($owner);

        $service->assertCanEditHorse($horse, $owner);

        self::assertTrue(true);
    }

    public function testAssertCanEditHorseThrowsExceptionForAttachedRiderWhoIsNotOwner(): void
    {
        $service = $this->createService();

        $owner = new AppUser();
        $attachedUser = new AppUser();
        $attachedRider = $this->createRiderForUser($attachedUser);

        $horse = new Horse();
        $horse->setOwner($owner);
        $horse->addRider($attachedRider);

        $this->expectException(AccessDeniedHttpException::class);

        $service->assertCanEditHorse($horse, $attachedUser);
    }

    public function testAssertCanEditHorseThrowsExceptionForUnrelatedUser(): void
    {
        $service = $this->createService();

        $owner = new AppUser();
        $unrelatedUser = new AppUser();

        $horse = new Horse();
        $horse->setOwner($owner);

        $this->expectException(AccessDeniedHttpException::class);

        $service->assertCanEditHorse($horse, $unrelatedUser);
    }

    public function testSavePersistsAndFlushesHorse(): void
    {
        $horse = new Horse();

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('persist')
            ->with($horse);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = $this->createService($entityManager);

        $service->save($horse);
    }

    public function testSetRestingUpdatesStatusAndFlushes(): void
    {
        $horse = new Horse();
        $horse->setStatus(Horse::STATUS_ACTIVE);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = $this->createService($entityManager);

        $service->setResting($horse);

        self::assertSame(Horse::STATUS_RESTING, $horse->getStatus());
    }

    public function testRetireUpdatesStatusAndFlushes(): void
    {
        $horse = new Horse();
        $horse->setStatus(Horse::STATUS_ACTIVE);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = $this->createService($entityManager);

        $service->retire($horse);

        self::assertSame(Horse::STATUS_RETIRED, $horse->getStatus());
    }

    public function testDeleteRemovesAndFlushesHorse(): void
    {
        $horse = new Horse();

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('remove')
            ->with($horse);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = $this->createService($entityManager);

        $service->delete($horse);
    }

    public function testUpdatePhotoDoesNothingWhenPhotoFileIsNull(): void
    {
        $horse = new Horse();
        $horse->setPhotoFilename('old-photo.jpg');

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $horseRepository = $this->createMock(HorseRepository::class);
        $slugger = $this->createMock(SluggerInterface::class);

        $slugger
            ->expects(self::never())
            ->method('slug');

        $service = new HorseService(
            entityManager: $entityManager,
            horseRepository: $horseRepository,
            slugger: $slugger,
            horsePhotosDirectory: '/tmp'
        );

        $service->updatePhoto($horse, null);

        self::assertSame('old-photo.jpg', $horse->getPhotoFilename());
    }

    private function createService(?EntityManagerInterface $entityManager = null): HorseService
    {
        return new HorseService(
            entityManager: $entityManager ?? $this->createMock(EntityManagerInterface::class),
            horseRepository: $this->createMock(HorseRepository::class),
            slugger: $this->createMock(SluggerInterface::class),
            horsePhotosDirectory: '/tmp'
        );
    }

    private function createRiderForUser(AppUser $user): Rider
    {
        $rider = new Rider();
        $rider->setFirstName('Marie');
        $rider->setLastName('Test');
        $rider->setAppUser($user);

        $user->setRider($rider);

        return $rider;
    }

    public function testGetVisibleHorsesForUserCanIncludeInactiveHorses(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $slugger = $this->createMock(SluggerInterface::class);
        $horseRepository = $this->createMock(HorseRepository::class);

        $user = new AppUser();
        $rider = $this->createRiderForUser($user);

        $expectedHorses = [
            new Horse(),
        ];

        $horseRepository
            ->expects(self::once())
            ->method('findForRider')
            ->with($rider, false)
            ->willReturn($expectedHorses);

        $horseRepository
            ->expects(self::never())
            ->method('findForOwner');

        $service = new HorseService(
            entityManager: $entityManager,
            horseRepository: $horseRepository,
            slugger: $slugger,
            horsePhotosDirectory: '/tmp'
        );

        self::assertSame($expectedHorses, $service->getVisibleHorsesForUser($user, false));
    }

    public function testArchiveUpdatesStatusAndFlushes(): void
    {
        $horse = new Horse();
        $horse->setStatus(Horse::STATUS_ACTIVE);

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $entityManager
            ->expects(self::once())
            ->method('flush');

        $service = $this->createService($entityManager);

        $service->archive($horse);

        self::assertSame(Horse::STATUS_ARCHIVED, $horse->getStatus());
    }
}