<?php

namespace App\Repositories;

use App\Models\User;

class GoalRepository
{
    public function getUserGoals(User $user)
    {
        return $user->goals;
    }
}
