<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;
use App\Entity\CompetitionEntry;
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

        $hasWitness = $result->declaredTotal !== null
            || $result->declaredPercentage !== null;

        $totalMatches = $result->declaredTotal !== null
            && abs($result->declaredTotal - $total) < 0.01;

        $percentageMatches = $result->declaredPercentage !== null
            && abs($result->declaredPercentage - $percentage) < 0.05;

        // Un témoin existe et contredit le calcul : lecture douteuse.
        // Aucun témoin lisible : on fait confiance au calcul.
        if ($hasWitness && !$totalMatches && !$percentageMatches) {
            return false;
        }

        return true;
    }

    /**
     * Recalcule les totaux à partir des notes présentes en base.
     *
     * @return bool true si toutes les notes sont renseignées
     */
    public function recalculate(Protocol $protocol): bool
    {
        $total = 0.0;
        $max = 0.0;
        $complete = true;

        foreach ($protocol->getProtocolFigureScores() as $figureScore) {
            $figure = $figureScore->getProtocolFigure();

            if ($figure === null || $figure->getMaxPoints() === null) {
                continue;
            }

            $coefficient = (float) $figure->getCoefficient();
            $max += (float) $figure->getMaxPoints() * $coefficient;

            $score = $figureScore->getScore();

            if ($score === null) {
                $complete = false;
                $figureScore->setFinalScore(null);
                continue;
            }

            $final = (float) $score * $coefficient;
            $figureScore->setFinalScore(number_format($final, 2, '.', ''));
            $total += $final;
        }

        $protocol->setMaxPoints(number_format($max, 2, '.', ''));

        if (!$complete) {
            $protocol->setTotalPoints(null);
            $protocol->setPercentage(null);

            return false;
        }

        $protocol->setTotalPoints(number_format($total, 2, '.', ''));
        $protocol->setPercentage(number_format($total / $max * 100, 3, '.', ''));

        return true;
    }

    /**
     * Moyenne des pourcentages des juges, uniquement si tous les
     * protocoles de l'épreuve sont complets.
     */
    public function updateEntryScore(CompetitionEntry $entry): void
    {
        $protocols = $entry->getProtocols();

        if ($protocols->isEmpty()) {
            $entry->setScorePercent(null);

            return;
        }

        $sum = 0.0;
        $count = 0;

        foreach ($protocols as $protocol) {
            if ($protocol->getStatus() !== Protocol::STATUS_ANALYZED
                || $protocol->getPercentage() === null
            ) {
                // Un juge incomplet : pas de moyenne, sinon elle serait fausse.
                $entry->setScorePercent(null);

                return;
            }

            $sum += (float) $protocol->getPercentage();
            $count++;
        }

        $entry->setScorePercent(number_format($sum / $count, 3, '.', ''));
    }

    private function clearExistingScores(Protocol $protocol): void
    {
        foreach ($protocol->getProtocolFigureScores() as $existing) {
            $this->em->remove($existing);
        }

        $this->em->flush();
    }
}