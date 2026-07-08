<?php

namespace App\Repositories;

use App\Models\User;

class UserStreakRepository
{
    public function getStreak(User $user)
    {
        return $user->streak;
    }

    public function createStreak(User $user, string $lastActive, ?int $currentStreak = null, ?int $longestStreak = null)
    {
        return $user->streak()->create(array_filter([
            'current_streak' => $currentStreak,
            'longest_streak' => $longestStreak,
            'last_active' => $lastActive,
        ]));
    }
}
