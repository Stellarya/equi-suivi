<?php

declare(strict_types=1);

namespace App\Tests\Service;

use App\Entity\CompetitionEntry;
use App\Entity\Protocol;
use App\Entity\ProtocolFigure;
use App\Entity\ProtocolFigureScore;
use App\Service\ProtocolAnalysisApplier;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

final class ProtocolAnalysisApplierTest extends TestCase
{
    public function testRecalculateAppliesCoefficients(): void
    {
        $protocol = new Protocol();

        // 7,0 en coefficient 1 puis 6,0 en coefficient 2 => 7 + 12 = 19 sur 30.
        $this->addScore($protocol, '7.00', coefficient: '1.00');
        $this->addScore($protocol, '6.00', coefficient: '2.00');

        $applier = new ProtocolAnalysisApplier($this->createMock(EntityManagerInterface::class));

        self::assertTrue($applier->recalculate($protocol));
        self::assertSame('19.00', $protocol->getTotalPoints());
        self::assertSame('30.00', $protocol->getMaxPoints());
        self::assertSame('63.333', $protocol->getPercentage());
    }

    public function testRecalculateRefusesToScoreAnIncompleteProtocol(): void
    {
        $protocol = new Protocol();

        $this->addScore($protocol, '7.00', coefficient: '1.00');
        $this->addScore($protocol, null, coefficient: '1.00');

        $applier = new ProtocolAnalysisApplier($this->createMock(EntityManagerInterface::class));

        // Une seule note manquante suffit : un pourcentage partiel serait
        // crédible et faux.
        self::assertFalse($applier->recalculate($protocol));
        self::assertNull($protocol->getTotalPoints());
        self::assertNull($protocol->getPercentage());
        self::assertSame('20.00', $protocol->getMaxPoints());
    }

    public function testUpdateEntryScoreAveragesJudgePercentages(): void
    {
        $entry = new CompetitionEntry();

        $entry->addProtocol($this->analyzedProtocol('C', '66.000'));
        $entry->addProtocol($this->analyzedProtocol('H', '70.000'));

        $applier = new ProtocolAnalysisApplier($this->createMock(EntityManagerInterface::class));
        $applier->updateEntryScore($entry);

        self::assertSame('68.000', $entry->getScorePercent());
    }

    public function testUpdateEntryScoreClearsAverageWhenAJudgeIsIncomplete(): void
    {
        $entry = new CompetitionEntry();

        $entry->addProtocol($this->analyzedProtocol('C', '66.000'));

        $pending = new Protocol();
        $pending->setJudgePosition('H');
        $pending->setStatus(Protocol::STATUS_NEEDS_REVIEW);
        $entry->addProtocol($pending);

        $applier = new ProtocolAnalysisApplier($this->createMock(EntityManagerInterface::class));
        $applier->updateEntryScore($entry);

        // Afficher la note d'un seul juge sur deux serait un score faux.
        self::assertNull($entry->getScorePercent());
    }

    private function analyzedProtocol(string $judgePosition, string $percentage): Protocol
    {
        $protocol = new Protocol();
        $protocol->setJudgePosition($judgePosition);
        $protocol->setStatus(Protocol::STATUS_ANALYZED);
        $protocol->setPercentage($percentage);

        return $protocol;
    }

    private function addScore(Protocol $protocol, ?string $score, string $coefficient): void
    {
        $figure = new ProtocolFigure();
        $figure->setNumber(1);
        $figure->setSection(ProtocolFigure::SECTION_TECHNICAL);
        $figure->setCoefficient($coefficient);
        $figure->setMaxPoints(10);

        $figureScore = new ProtocolFigureScore();
        $figureScore->setProtocolFigure($figure);
        $figureScore->setScore($score);

        $protocol->addProtocolFigureScore($figureScore);
    }
}