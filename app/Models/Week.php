<?php

namespace App\Models;

use Database\Factories\WeekFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable('available_minutes', 'week_start_date', 'user_id')]
#[WithoutTimestamps]
class Week extends Model
{
    /** @use HasFactory<WeekFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'week_start_date' => 'datetime:Y-m-d',
        ];
    }
}
