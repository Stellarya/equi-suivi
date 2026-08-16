<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;

interface ProtocolAnalyzerInterface
{

    /**
     * @param array<string, int[]> $expectedFigures section => numéros
     */
    public function analyze(string $filePath, array $expectedFigures, string $judgePosition): AnalysisResult;
}