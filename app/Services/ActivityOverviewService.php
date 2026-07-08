<?php

namespace App\Services;

use App\Data\ActivityOverview\ActivityOverviewData;
use App\Data\ActivityOverview\DayLog;
use App\Models\TimeLog;
use App\Models\User;
use App\Repositories\TimeLogRepository;
use App\Repositories\UserStreakRepository;
use App\Repositories\WeekRepository;
use Illuminate\Support\Collection;

class ActivityOverviewService
{
    public function __construct(
        protected WeekRepository $weekRepository,
        protected TimeLogRepository $timeLogRepository,
        protected UserStreakRepository $userStreakRepository,
        protected UserStreakService $userStreakService
    ) {}

    public function getOverview(User $user): ActivityOverviewData
    {
        $totalWeeksThisYear = $this->weekRepository->getWeeksCountThisYear($user);
        $streak = $this->userStreakRepository->getStreak($user);
        $mostActiveWeekDay = $this->userStreakService->mostActiveWeekDay($user);
        $activityLogsThisYear = $this->calculateActivityLogs(
            $this->timeLogRepository->getActivityLogsThisYear($user)
        );

        return new ActivityOverviewData(
            totalWeeksThisYear: $totalWeeksThisYear,
            longestStreak: $streak->longest_streak,
            currentStreak: $streak->current_streak,
            mostActiveWeekDay: $mostActiveWeekDay,
            activityLogsThisYear: $activityLogsThisYear
        );
    }

    /** @param Collection<int, TimeLog> $timeLogs */
    private function calculateActivityLogs(Collection $timeLogs)
    {
        $dayCounts = $timeLogs->pluck('count');

        $q1 = $this->percentile($dayCounts, 0.25);
        $q2 = $this->percentile($dayCounts, 0.50);
        $q3 = $this->percentile($dayCounts, 0.75);

        return $timeLogs->map(function (TimeLog $timeLog) use ($q1, $q2, $q3) {
            /** @var int */
            $dayCount = $timeLog->count;

            if ($dayCount === 0) {
                $level = 0;
            } elseif ($dayCount <= $q1) {
                $level = 1;
            } elseif ($dayCount <= $q2) {
                $level = 2;
            } elseif ($dayCount <= $q3) {
                $level = 3;
            } else {
                $level = 4;
            }

            return new DayLog(
                level: $level,
                count: $dayCount,
                date: $timeLog->date
            );
        });
    }

    /** @param Collection<int, int> $values */
    private function percentile(Collection $values, float $percentile): float
    {
        $values = $values->sort()->values();
        $count = $values->count();

        if ($count === 0) {
            return 0;
        }

        if ($count === 1) {
            return $values->first();
        }

        $index = ($count - 1) * $percentile;

        $lowerIndex = (int) floor($index);
        $upperIndex = (int) ceil($index);

        if ($lowerIndex === $upperIndex) {
            return $values[$lowerIndex];
        }

        $fraction = $index - $lowerIndex;

        return $values[$lowerIndex]
            + (($values[$upperIndex] - $values[$lowerIndex]) * $fraction);
    }
}
