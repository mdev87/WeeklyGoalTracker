<?php

namespace App\Services;

use App\Data\Dashboard\DashboardData;
use App\Models\User;
use App\Repositories\UserStreakRepository;

class DashboardService
{
    public function __construct(
        protected WeeklyStatsService $weeklyStatsService,
        protected UserStreakRepository $userStreakRepository
    ) {}

    public function get(User $user): DashboardData
    {
        return new DashboardData(
            activeStreakDays: $this->userStreakRepository->getStreak($user)->current_streak,

            weeklyStats: $this->weeklyStatsService
                ->getStats($user)
        );
    }
}
