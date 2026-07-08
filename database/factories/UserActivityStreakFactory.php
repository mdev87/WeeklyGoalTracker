<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserActivityStreak;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<UserActivityStreak>
 */
class UserActivityStreakFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $currentStreak = fake()->numberBetween(1, 30);

        return [
            'current_streak' => $currentStreak,
            'longest_streak' => fake()->numberBetween($currentStreak, 50),
            'last_active' => jdate()->format('Y-m-d'),
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
