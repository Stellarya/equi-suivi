<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;
use App\Dto\FigureReading;
use Override;

final class FakeProtocolAnalyzer implements ProtocolAnalyzerInterface
{
    #[Override]
    public function analyze(string $filePath, array $expectedFigures, string $judgePosition): AnalysisResult
    {
        $figures = [];

        foreach ($expectedFigures as $section => $numbers) {
            foreach ($numbers as $number) {
                $figures[] = new FigureReading(
                    section: $section,
                    number: $number,
                    score: 6.5,
                    comment: sprintf('Commentaire simulé pour %s %d', $section, $number),
                    confidence: 0.95,
                );
            }
        }

         return new AnalysisResult(
            judgePosition: $judgePosition,
            testLabel: 'Reprise simulée',
            generalComment: 'Analyse simulée, aucun appel réseau.',
            figures: $figures,
        );
    }
}