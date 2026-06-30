<?php

namespace App\Data\Dashboard;

readonly class GoalStatsData
{
    public function __construct(
        public GoalData $goal,
        public int $plannedMinutes,
        public int $spentMinutes,
        public int $remainingMinutes,
        public float $completionPercentage,
    ) {}
}
