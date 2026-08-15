<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;
use App\Entity\Protocol;
use App\Entity\ProtocolFigure;
use App\Entity\ProtocolFigureScore;
use App\Exception\ProtocolAnalysisException;
use Doctrine\ORM\EntityManagerInterface;

final class ProtocolAnalysisApplier
{
    private const CONFIDENCE_THRESHOLD = 0.7;
    private const SCORE_STEP = 0.5;

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {}

    /**
     * @param ProtocolFigure[] $activeFigures
     * @return bool true si tout est exploitable, false s'il faut une relecture
     */
    public function apply(Protocol $protocol, AnalysisResult $result, array $activeFigures): bool
    {
        $figuresByNumber = [];
        foreach ($activeFigures as $figure) {
            if ($figure->getMaxPoints() === null) {
                throw new ProtocolAnalysisException(
                    sprintf('La figure %d n\'a pas de barème (maxPoints).', $figure->getNumber())
                );
            }
            $figuresByNumber[$figure->getNumber()] = $figure;
        }

        $readingsByNumber = [];
        foreach ($result->figures as $reading) {
            if (!isset($figuresByNumber[$reading->number])) {
                throw new ProtocolAnalysisException(
                    sprintf('Figure %d inconnue dans cette reprise.', $reading->number)
                );
            }
            $readingsByNumber[$reading->number] = $reading;
        }

        $missing = array_diff(array_keys($figuresByNumber), array_keys($readingsByNumber));
        if ($missing !== []) {
            throw new ProtocolAnalysisException(
                sprintf('Figures absentes de la lecture : %s.', implode(', ', $missing))
            );
        }

        $this->clearExistingScores($protocol);

        $total = 0.0;
        $max = 0.0;
        $needsReview = false;

        foreach ($figuresByNumber as $number => $figure) {
            $reading = $readingsByNumber[$number];
            $coefficient = (float) $figure->getCoefficient();
            $figureMax = (float) $figure->getMaxPoints();

            $max += $figureMax * $coefficient;

            $figureScore = new ProtocolFigureScore();
            $figureScore->setProtocol($protocol);
            $figureScore->setProtocolFigure($figure);
            $figureScore->setComment($reading->comment);

            if ($reading->score === null || $reading->confidence < self::CONFIDENCE_THRESHOLD) {
                $needsReview = true;
                $this->em->persist($figureScore);
                continue;
            }

            if ($reading->score < 0.0 || $reading->score > $figureMax) {
                throw new ProtocolAnalysisException(
                    sprintf('Note %s hors barème pour la figure %d.', $reading->score, $number)
                );
            }

            if (fmod($reading->score, self::SCORE_STEP) !== 0.0) {
                throw new ProtocolAnalysisException(
                    sprintf('Note %s non conforme au pas de %s.', $reading->score, self::SCORE_STEP)
                );
            }

            $final = $reading->score * $coefficient;

            $figureScore->setScore(number_format($reading->score, 2, '.', ''));
            $figureScore->setFinalScore(number_format($final, 2, '.', ''));

            $total += $final;
            $this->em->persist($figureScore);
        }

        $protocol->setGeneralComment($result->generalComment);
        $protocol->setMaxPoints(number_format($max, 2, '.', ''));

        if ($needsReview) {
            return false;
        }

        $protocol->setTotalPoints(number_format($total, 2, '.', ''));
        $protocol->setPercentage(number_format($total / $max * 100, 3, '.', ''));

        return true;
    }

    private function clearExistingScores(Protocol $protocol): void
    {
        foreach ($protocol->getProtocolFigureScores() as $existing) {
            $this->em->remove($existing);
        }
        $this->em->flush();
    }
}