<?php

namespace App\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Morilog\Jalali\Jalalian;

class UpdateActiveStreakAction
{
    public function execute(User $user): int
    {
        $cacheKeyDays = "user_{$user->id}_active_streak_days";
        $cacheKeyLastDate = "user_{$user->id}_last_active_date";

        $activeDays = Cache::get($cacheKeyDays, 1);

        $lastActiveDateString = Cache::get($cacheKeyLastDate);
        $lastActiveDate = $lastActiveDateString
            ? Jalalian::fromFormat('Y-m-d', $lastActiveDateString)
            : null;

        if ($lastActiveDate && $lastActiveDate->isToday()) {
            return $activeDays;
        }

        if ($lastActiveDate && $lastActiveDate->isYesterday()) {
            $activeDays++;
        } else {
            $activeDays = 1;
        }

        Cache::put($cacheKeyDays, $activeDays, now()->addWeek());
        Cache::put($cacheKeyLastDate, jdate()->format('Y-m-d'), now()->addWeek());

        return $activeDays;
    }
}
