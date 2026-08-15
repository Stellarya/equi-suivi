<?php

declare(strict_types=1);

namespace App\Dto;

final readonly class FigureReading
{
    public function __construct(
        public int $number,
        public ?float $score,
        public ?string $comment,
        public float $confidence
    )
    {}
}