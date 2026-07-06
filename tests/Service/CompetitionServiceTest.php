<?php

namespace App\Tests\Unit\Service;

use App\Entity\Competition;
use App\Entity\StatusCompetition;
use App\Repository\CompetitionRepository;
use App\Service\CompetitionService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class CompetitionServiceTest extends TestCase
{
    public function testSaveCompetitionAddsDefaultStatus(): void
    {
        $competition = new Competition();

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(CompetitionRepository::class);

        $entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($competition);

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $service = new CompetitionService(
            $entityManager,
            $repository
        );

        $service->saveCompetition($competition);

        $this->assertInstanceOf(
            StatusCompetition::class,
            $competition->getStatusCompetition()
        );

        $this->assertEquals(
            'EN_ATTENTE',
            $competition->getStatusCompetition()->getMnemonique()
        );
    }

    public function testSaveCompetitionKeepsExistingStatus(): void
    {
        $competition = new Competition();

        $status = new StatusCompetition();
        $status->setMnemonique('VALIDE');

        $competition->setStatusCompetition($status);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(CompetitionRepository::class);

        $entityManager
            ->expects($this->once())
            ->method('persist');

        $entityManager
            ->expects($this->once())
            ->method('flush');

        $service = new CompetitionService(
            $entityManager,
            $repository
        );

        $service->saveCompetition($competition);

        $this->assertSame(
            $status,
            $competition->getStatusCompetition()
        );
    }

    public function testGetAllCompetitionsReturnsRepositoryResult(): void
    {
        $competitions = [
            new Competition(),
            new Competition(),
        ];

        $entityManager = $this->createMock(EntityManagerInterface::class);

        $repository = $this->createMock(CompetitionRepository::class);

        $repository
            ->expects($this->once())
            ->method('findBy')
            ->with([], ['startDate' => 'DESC'])
            ->willReturn($competitions);

        $service = new CompetitionService(
            $entityManager,
            $repository
        );

        $result = $service->getAllCompetitions();

        $this->assertSame($competitions, $result);
    }
}