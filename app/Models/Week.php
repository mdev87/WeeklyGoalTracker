<?php

namespace App\Models;

use Database\Factories\WeekFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable('planned_minutes', 'week_start_date', 'user_id')]
#[WithoutTimestamps]
class Week extends Model
{
    /** @use HasFactory<WeekFactory> */
    use HasFactory;

    #[Scope]
    protected function thisWeek(Builder $query)
    {
        $query->where('week_start_date', jdate()->getFirstDayOfWeek()->format('Y-m-d'));
    }
}
