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
        ], ['planned_minutes' => $defaultPlannedMinutes]);
    }
}
