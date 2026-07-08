<?php

namespace App\Data\ActivityOverview;

use Illuminate\Support\Collection;

readonly class ActivityOverviewData
{
    /** @param Collection<int, DayLog> $activityLogsThisYear */
    public function __construct(
        public int $totalWeeksThisYear,
        public int $longestStreak,
        public int $currentStreak,
        public ?string $mostActiveWeekDay,
        public Collection $activityLogsThisYear,
    ) {}
}
