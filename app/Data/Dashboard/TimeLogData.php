<?php

namespace App\Data\Dashboard;

readonly class TimeLogData
{
    public function __construct(
        public int $id,
        public int $durationMinutes,
        public GoalData $goal
    ) {}
}
