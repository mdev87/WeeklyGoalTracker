<?php

namespace App\Models;

use App\Observers\TimeLogObserver;
use App\Policies\TimeLogPolicy;
use Database\Factories\TimeLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $duration_minutes
 * @property string $date
 * @property string|null $note
 * @property int $goal_id
 * @property int $user_id
 * @property-read Goal $goal
 *
 * @method static \Database\Factories\TimeLogFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog whereDurationMinutes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog whereGoalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|TimeLog whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['duration_minutes', 'date', 'note', 'goal_id', 'user_id'])]
#[WithoutTimestamps]
#[ObservedBy([TimeLogObserver::class])]
#[UsePolicy(TimeLogPolicy::class)]
class TimeLog extends Model
{
    /** @use HasFactory<TimeLogFactory> */
    use HasFactory;

    public function goal()
    {
        return $this->belongsTo(Goal::class);
    }
}
