<?php

namespace App\Repositories;

use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TimeLogRepository
{
    public function getWeekLogs(User $user, string $start, string $end)
    {
        /** @var Builder<TimeLog> */
        $query = $user->timeLogs()->getQuery()
            ->whereBetween('date', [$start, $end]);

        return $query->with('goal')->get();
    }

    public function getActivityLogsThisYear(User $user)
    {
        return $user->timeLogs()
            ->whereBetween('date', [
                jdate()->getFirstDayOfYear()->format('Y-m-d'),
                jdate()->getEndDayOfYear()->format('Y-m-d'),
            ])
            ->groupBy('date')
            ->get(['date', DB::raw('COUNT(*) as count')]);
    }

    public function getSpentMinutes(User $user, string $start, string $end): int
    {
        return (int) $user->timeLogs()
            ->whereBetween('date', [$start, $end])
            ->sum('duration_minutes');
    }

    public function getSpentMinutesByGoal(User $user, string $start, string $end)
    {
        return $user->timeLogs()
            ->whereBetween('date', [$start, $end])
            ->selectRaw('goal_id, SUM(duration_minutes) as spent_minutes')
            ->groupBy('goal_id')
            ->pluck('spent_minutes', 'goal_id');
    }
}
