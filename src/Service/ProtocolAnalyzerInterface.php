<?php

declare(strict_types=1);

namespace App\Service;

use App\Dto\AnalysisResult;

interface ProtocolAnalyzerInterface
{

    public function analyze(string $filePath, array $expectedFigureNumbers, string $judgePosition): AnalysisResult;
}