<?php

namespace App\Data\Analysis;

class InfoItem
{
    public function __construct(
        public string $title,
        public string $description
    ) {}
}
