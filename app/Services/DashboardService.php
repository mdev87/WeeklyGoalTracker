<?php

namespace App\Services;

use App\Actions\UpdateActiveStreakAction;
use App\Data\Dashboard\DashboardData;
use App\Models\User;

class DashboardService
{
    public function __construct(
        protected UpdateActiveStreakAction $updateActiveStreak,
        protected WeeklyStatsService $weeklyStatsService
    ) {}

    public function get(User $user): DashboardData
    {
        return new DashboardData(
            activeStreakDays: $this->updateActiveStreak
                ->execute($user),

            weeklyStats: $this->weeklyStatsService
                ->getStats($user)
        );
    }
}
