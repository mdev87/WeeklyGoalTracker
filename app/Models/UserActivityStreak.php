<?php

namespace App\Models;

use Database\Factories\UserActivityStreakFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property int $current_streak
 * @property int $longest_streak
 * @property string $last_active
 * @property int $user_id
 *
 * @method static \Database\Factories\UserActivityStreakFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak whereCurrentStreak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak whereLastActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak whereLongestStreak($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|UserActivityStreak whereUserId($value)
 *
 * @mixin \Eloquent
 */
#[Fillable(['current_streak', 'longest_streak', 'last_active', 'user_id'])]
#[WithoutTimestamps]
class UserActivityStreak extends Model
{
    /** @use HasFactory<UserActivityStreakFactory> */
    use HasFactory;
}
