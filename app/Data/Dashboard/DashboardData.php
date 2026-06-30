<?php

namespace App\Data\Dashboard;

readonly class DashboardData
{
    public function __construct(
        public int $activeStreakDays,
        public WeeklyStatsData $weeklyStats,
    ) {}
}
