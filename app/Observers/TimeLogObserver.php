<?php

namespace App\Observers;

use App\Models\TimeLog;
use App\Models\User;
use App\Services\UserStreakService;
use Illuminate\Support\Facades\Cache;

class TimeLogObserver
{
    /**
     * Handle the TimeLog "created" event.
     */
    public function created(TimeLog $timeLog): void
    {
        $today = jdate()->format('Y-m-d');
        $cacheKey = "user{$timeLog->user_id}_timelogs_count_{$today}";

        /** @var bool */
        $isFirstToday = Cache::remember(
            $cacheKey,
            now()->endOfDay(),
            fn () => TimeLog::whereUserId($timeLog->user_id)
                ->where('date', jdate()->format('Y-m-d'))
                ->exists()
        );

        if ($isFirstToday) {
            app(UserStreakService::class)->incrementForUser(User::find($timeLog->user_id));
        }
    }
}
