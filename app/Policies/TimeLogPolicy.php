<?php

namespace App\Policies;

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;

class TimeLogPolicy
{
    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, int $goalId): bool
    {
        return Goal::whereKey($goalId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TimeLog $timeLog, ?int $goalId): bool
    {
        if ($timeLog->user_id !== $user->id) {
            return false;
        }

        if ($goalId === null) {
            return true;
        }

        return Goal::whereKey($goalId)
            ->where('user_id', $user->id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TimeLog $timeLog): bool
    {
        return $timeLog->user_id === $user->id;
    }
}
