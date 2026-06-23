<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Week;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Week>
 */
class WeekFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'available_minutes' => fake()->randomElement([
                60,
                90,
                120,
                180,
                300,
                480,
                600,
                720,
                900,
            ]),
            'week_start_date' => now()->startOfWeek()->format('Y-m-d'),
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
