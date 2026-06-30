<?php

namespace App\Services;

use App\Data\Dashboard\GoalData;
use App\Data\Dashboard\GoalStatsData;
use App\Data\Dashboard\TimeLogData;
use App\Data\Dashboard\WeeklyStatsData;
use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use App\Repositories\GoalRepository;
use App\Repositories\TimeLogRepository;
use App\Repositories\WeekRepository;
use Illuminate\Support\Collection;
use Morilog\Jalali\Jalalian;

class WeeklyStatsService
{
    public function __construct(
        protected WeekRepository $weekRepository,
        protected GoalRepository $goalRepository,
        protected TimeLogRepository $timeLogRepository
    ) {}

    public function getStats(User $user)
    {
        $thisWeek = $this->weekRepository->getCurrentWeek($user, config('week.default_planned_minutes'));

        $startDate = $thisWeek->week_start_date;
        $endDate = Jalalian::fromFormat('Y-m-d', $thisWeek->week_start_date)
            ->getEndDayOfWeek()->format('Y-m-d');

        $timeLogs = $this->timeLogRepository->getWeekLogs($user, $startDate, $endDate);

        $plannedMinutes = $thisWeek->planned_minutes;
        $spentMinutes = $timeLogs->sum('duration_minutes');
        $remainingMinutes = $plannedMinutes - $spentMinutes;
        $completionPercentage = $plannedMinutes > 0 ? (100 * $spentMinutes / $plannedMinutes) : 0;

        return new WeeklyStatsData(
            plannedMinutes: $plannedMinutes,
            spentMinutes: $spentMinutes,
            remainingMinutes: $remainingMinutes,
            completionPercentage: $completionPercentage,
            goalStats: $this->calculateGoalStats($user, $plannedMinutes, $timeLogs),
            todayLogs: $this->todayTimeLogs($timeLogs)
        );
    }

    /**
     * @param  Collection<int, TimeLog>  $timeLogs
     * @return Collection<int, GoalStatsData>
     */
    private function calculateGoalStats(User $user, int $weeklyPlannedMinutes, Collection $timeLogs)
    {
        return $this->goalRepository->getUserGoals($user)
            ->map(function (Goal $goal) use ($weeklyPlannedMinutes, $timeLogs) {

                $plannedMinutes = $goal->priority_percentage * $weeklyPlannedMinutes / 100;
                $spentMinutes = $timeLogs->where('goal_id', $goal->id)->sum('duration_minutes');
                $remainingMinutes = $plannedMinutes - $spentMinutes;
                $completionPercentage = $plannedMinutes > 0 ? (100 * $spentMinutes / $plannedMinutes) : 0;

                return new GoalStatsData(
                    goal: new GoalData(
                        $goal->id,
                        $goal->name,
                        $goal->color
                    ),
                    plannedMinutes: $plannedMinutes,
                    spentMinutes: $spentMinutes,
                    remainingMinutes: $remainingMinutes,
                    completionPercentage: $completionPercentage
                );
            });
    }

    private function todayTimeLogs(Collection $logs)
    {
        return $logs->where('date', jdate()->format('Y-m-d'))
            ->map(fn (TimeLog $log) => new TimeLogData(
                id: $log->id,
                durationMinutes: $log->duration_minutes,
                goal: new GoalData(
                    $log->goal->id,
                    $log->goal->name,
                    $log->goal->color
                )
            ));
    }
}
