<?php

namespace App\Data\Dashboard;

use Illuminate\Support\Collection;

readonly class WeeklyStatsData
{
    /**
     * @param  Collection<int, GoalStatsData>  $goalStats
     * @param  Collection<int, TimeLogData>  $todayLogs
     */
    public function __construct(
        public int $plannedMinutes,
        public int $spentMinutes,
        public int $remainingMinutes,
        public float $completionPercentage,
        public Collection $goalStats,
        public Collection $todayLogs
    ) {}
}
