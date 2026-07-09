<?php

namespace App\Data\Analysis;

use Illuminate\Support\Collection;

class AnalysisData
{
    /** @param Collection<int, CompareItem> $compares */
    public function __construct(
        public string $weekSummary,
        public string $date,
        public InfoItem $strongestGoal,
        public InfoItem $weakestGoal,
        public InfoItem $weekOffer,
        public InfoItem $weeklyProgress,
        public Collection $compares
    ) {}
}
