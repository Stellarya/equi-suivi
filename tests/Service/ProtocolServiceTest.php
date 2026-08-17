<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\AppUser;
use App\Entity\CompetitionEntry;
use App\Entity\CompetitionRegistration;
use App\Entity\Horse;
use App\Entity\Rider;
use App\Service\ProtocolService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

final class ProtocolServiceTest extends TestCase
{
    public function testRiderOfTheRegistrationIsAllowed(): void
    {
        $rider = new Rider();

        $user = new AppUser();
        $user->setRider($rider);

        $registration = new CompetitionRegistration();
        $registration->setRider($rider);

        $entry = new CompetitionEntry();
        $entry->setCompetitionRegistration($registration);

        $this->service()->assertCanManageProtocol($entry, $user);

        $this->expectNotToPerformAssertions();
    }

    public function testHorseOwnerIsAllowed(): void
    {
        $owner = new AppUser();

        $horse = new Horse();
        $horse->setOwner($owner);

        $registration = new CompetitionRegistration();
        $registration->setRider(new Rider());
        $registration->setHorse($horse);

        $entry = new CompetitionEntry();
        $entry->setCompetitionRegistration($registration);

        $this->service()->assertCanManageProtocol($entry, $owner);

        $this->expectNotToPerformAssertions();
    }

    public function testUnrelatedUserIsDenied(): void
    {
        $registration = new CompetitionRegistration();
        $registration->setRider(new Rider());
        $registration->setHorse(new Horse());

        $entry = new CompetitionEntry();
        $entry->setCompetitionRegistration($registration);

        $intruder = new AppUser();
        $intruder->setRider(new Rider());

        $this->expectException(AccessDeniedHttpException::class);

        $this->service()->assertCanManageProtocol($entry, $intruder);
    }

    public function testEntryWithoutRegistrationIsDenied(): void
    {
        $this->expectException(AccessDeniedHttpException::class);

        $this->service()->assertCanManageProtocol(new CompetitionEntry(), new AppUser());
    }

    private function service(): ProtocolService
    {
        return new ProtocolService(
            $this->createMock(EntityManagerInterface::class),
            new AsciiSlugger(),
            $this->createMock(MessageBusInterface::class),
            sys_get_temp_dir(),
        );
    }
}