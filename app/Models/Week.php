<?php

namespace App\Models;

use Database\Factories\WeekFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $planned_minutes
 * @property string $week_start_date
 * @property int $user_id
 *
 * @method static \Database\Factories\WeekFactory factory($count = null, $state = [])
 * @method static Builder<static>|Week newModelQuery()
 * @method static Builder<static>|Week newQuery()
 * @method static Builder<static>|Week query()
 * @method static Builder<static>|Week thisWeek()
 * @method static Builder<static>|Week whereAvailableMinutes($value)
 * @method static Builder<static>|Week whereId($value)
 * @method static Builder<static>|Week whereUserId($value)
 * @method static Builder<static>|Week whereWeekStartDate($value)
 *
 * @mixin \Eloquent
 */
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
