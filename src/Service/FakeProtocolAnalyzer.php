<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;
use App\Dto\FigureReading;
use Override;

final class FakeProtocolAnalyzer implements ProtocolAnalyzerInterface
{
    #[Override]
    public function analyze(string $filePath, array $expectedFigureNumbers): AnalysisResult
    {
        $figures = [];

        foreach ($expectedFigureNumbers as $number) {
            $figures[] = new FigureReading(
                number: $number,
                score: 6.5,
                comment: sprintf('Commentaire simulé pour la figue %d', $number),
                confidence: 0.95
            );
        }

         return new AnalysisResult(
            judgePosition: 'C',
            testLabel: 'Reprise simulée',
            generalComment: 'Analyse simulée, aucun appel réseau.',
            figures: $figures,
        );
    }
}