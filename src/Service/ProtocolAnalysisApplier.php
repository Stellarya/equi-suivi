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
        $figuresByKey = [];

        foreach ($activeFigures as $figure) {
            if ($figure->getMaxPoints() === null) {
                throw new ProtocolAnalysisException(
                    sprintf('La figure %s %d n\'a pas de barème.', $figure->getSection(), $figure->getNumber())
                );
            }

            $figuresByKey[$figure->getSection() . ':' . $figure->getNumber()] = $figure;
        }

        $readingsByKey = [];

        foreach ($result->figures as $reading) {
            $key = $reading->section . ':' . $reading->number;

            if (!isset($figuresByKey[$key])) {
                throw new ProtocolAnalysisException(sprintf('Figure %s inconnue dans cette reprise.', $key));
            }

            $readingsByKey[$key] = $reading;
        }

        $missing = array_diff(array_keys($figuresByKey), array_keys($readingsByKey));

        if ($missing !== []) {
            throw new ProtocolAnalysisException(
                sprintf('Figures absentes de la lecture : %s.', implode(', ', $missing))
            );
        }

        $this->clearExistingScores($protocol);

        $total = 0.0;
        $max = 0.0;
        $needsReview = false;

        foreach ($figuresByKey as $key => $figure) {
            $reading = $readingsByKey[$key];
            $coefficient = (float) $figure->getCoefficient();
            $figureMax = (float) $figure->getMaxPoints();

            $max += $figureMax * $coefficient;

            $figureScore = new ProtocolFigureScore();
            $figureScore->setProtocol($protocol);
            $figureScore->setProtocolFigure($figure);
            $figureScore->setComment($reading->comment);

            $score = $reading->score;

            // Rien de lu : ligne créée sans note, à saisir manuellement.
            if ($score === null) {
                $needsReview = true;
                $this->em->persist($figureScore);
                continue;
            }

            // Note aberrante : défaut de lecture, pas une erreur technique.
            if ($score < 0.0 || $score > $figureMax || fmod($score, self::SCORE_STEP) !== 0.0) {
                $needsReview = true;
                $this->em->persist($figureScore);
                continue;
            }

            // Note plausible : on la conserve dans tous les cas.
            $figureScore->setScore(number_format($score, 2, '.', ''));

            // Lecture peu sûre : note gardée, mais à confirmer avant de compter.
            if ($reading->confidence < self::CONFIDENCE_THRESHOLD) {
                $needsReview = true;
                $this->em->persist($figureScore);
                continue;
            }

            $final = $score * $coefficient;
            $figureScore->setFinalScore(number_format($final, 2, '.', ''));

            $total += $final;
            $this->em->persist($figureScore);
        }

        $protocol->setGeneralComment($result->generalComment);
        $protocol->setMaxPoints(number_format($max, 2, '.', ''));

        if ($needsReview) {
            return false;
        }

        $percentage = $total / $max * 100;

        $protocol->setTotalPoints(number_format($total, 2, '.', ''));
        $protocol->setPercentage(number_format($percentage, 3, '.', ''));

        $totalMatches = $result->declaredTotal !== null
            && abs($result->declaredTotal - $total) < 0.01;

        $percentageMatches = $result->declaredPercentage !== null
            && abs($result->declaredPercentage - $percentage) < 0.05;

        // Aucun témoin ne confirme : lecture douteuse, on fait relire.
        if (!$totalMatches && !$percentageMatches) {
            return false;
        }

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