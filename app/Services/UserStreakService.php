<?php

namespace App\Services;

use App\Models\TimeLog;
use App\Models\User;
use App\Repositories\UserStreakRepository;
use Morilog\Jalali\Jalalian;

class UserStreakService
{
    public function __construct(
        protected UserStreakRepository $userStreakRepository
    ) {}

    public function incrementForUser(User $user)
    {
        $streak = $this->userStreakRepository->getStreak($user);
        if ($streak === null) {
            $this->userStreakRepository->createStreak($user, jdate()->format('Y-m-d'));

            return;
        }

        $lastActiveDateAsJalali = Jalalian::fromFormat('Y-m-d', $streak->last_active);

        if ($lastActiveDateAsJalali->isYesterday()) {
            $streak->current_streak++;
            $streak->longest_streak = max($streak->current_streak, $streak->longest_streak);
        } elseif (! $lastActiveDateAsJalali->isToday()) {
            $streak->current_streak = 1;
        }

        $streak->last_active = jdate()->format('Y-m-d');
        $streak->saveOrFail();
    }

    public function mostActiveWeekDay(User $user)
    {
        $timeLogs = $user->timeLogs()->getQuery()
            ->whereBetween('date', [
                jdate()->getFirstDayOfWeek()->format('Y-m-d'),
                jdate()->format('Y-m-d'),
            ])->get();

        if ($timeLogs->isEmpty()) {
            return null;
        }

        $dayCounts = [];
        $timeLogs->each(function (TimeLog $timeLog) use (&$dayCounts) {
            $dayName = Jalalian::fromFormat('Y-m-d', $timeLog->date)->getDayOfWeek();
            $dayCounts[$dayName] = ($dayCounts[$dayName] ?? 0) + 1;
        });

        $maxCount = max($dayCounts);
        $mostActiveDay = array_search($maxCount, $dayCounts);

        return is_int($mostActiveDay) ? match ($mostActiveDay) {
            0 => 'sat',
            1 => 'sun',
            2 => 'mon',
            3 => 'tue',
            4 => 'wed',
            5 => 'thu',
            6 => 'fri',
        } : null;
    }
}
