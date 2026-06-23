<?php

namespace App\Models;

use Database\Factories\GoalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\WithoutTimestamps;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['name', 'color', 'priority_percentage', 'user_id'])]
#[WithoutTimestamps]
class Goal extends Model
{
    /** @use HasFactory<GoalFactory> */
    use HasFactory;
}
