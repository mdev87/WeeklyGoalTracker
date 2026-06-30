<?php

namespace App\Repositories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

class GoalRepository
{
    /** @return Collection<Goal> */
    public function getUserGoals(User $user): Collection
    {
        return $user->goals()->get();
    }
}
