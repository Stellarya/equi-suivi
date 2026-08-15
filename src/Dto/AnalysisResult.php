<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class AnalysisResult
{
    /**
     * @param FigureReading[] $figures
     */
    public function __construct(
        public string $judgePosition,
        public ?string $testLabel,
        public ?string $generalComment,
        public array $figures
    )
    {}
}