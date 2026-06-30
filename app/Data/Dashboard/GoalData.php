<?php

namespace App\Data\Dashboard;

readonly class GoalData
{
    public function __construct(
        public int $id,
        public string $name,
        public string $color,
    ) {}
}
