<?php

namespace App\Data\Analysis;

class CompareItem
{
    public function __construct(
        public string $goalName,
        public int $lastWeekPlannedMinutes,
        public int $lastWeekSpentMinutes,
        public int $thisWeekPlannedMinutes,
        public int $thisWeekSpentMinutes,
        public int $differenceMinutes
    ) {}
}
