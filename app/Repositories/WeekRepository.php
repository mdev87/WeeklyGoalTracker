<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\Week;

class WeekRepository
{
    public function getCurrentWeek(User $user, int $defaultPlannedMinutes): Week
    {
        return Week::firstOrCreate([
            'user_id' => $user->id,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        ], [
            'planned_minutes' => $defaultPlannedMinutes,
        ]);
    }

    public function getPreviousWeek(User $user): ?Week
    {
        return $user->weeks()
            ->where('week_start_date', jdate()->getFirstDayOfWeek()->subDays(7)->format('Y-m-d'))
            ->first();
    }

    public function getWeeksCountThisYear(User $user): int
    {
        return $user->weeks()
            ->whereBetween('week_start_date', [
                jdate()->getFirstDayOfYear()->format('Y-m-d'),
                jdate()->getEndDayOfYear()->format('Y-m-d'),
            ])
            ->count();
    }
}
