<?php

namespace App\Data\ActivityOverview;

readonly class DayLog
{
    public function __construct(
        public int $level,
        public int $count,
        public string $date
    ) {}
}
