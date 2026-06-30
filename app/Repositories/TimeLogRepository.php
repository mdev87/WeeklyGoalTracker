<?php

namespace App\Repositories;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class TimeLogRepository
{
    /** @return Collection<TimeLog> */
    public function getWeekLogs(User $user, string $start, string $end): Collection
    {
        /** @var Builder<TimeLog> */
        $query = $user->timeLogs()->getQuery()
            ->whereBetween('date', [$start, $end]);

        return $query->with('goal')->get();
    }
}
