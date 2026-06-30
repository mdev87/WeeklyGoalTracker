<?php

namespace Database\Seeders;

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\Week;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Eloquent\Factories\Sequence;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create(['name' => 'Mohammad Taha', 'password' => '123'])
            ->each(function ($user) {
                Week::factory(5)
                    ->state(new Sequence(fn (Sequence $sequence) => [
                        'user_id' => $user->id,
                        'week_start_date' => jdate()
                            ->getFirstDayOfWeek()
                            ->subDays(7 * $sequence->index)
                            ->format('Y-m-d'),
                    ]))
                    ->create()
                    ->each(function ($week) use ($user) {
                        Goal::factory(3)->state([
                            'user_id' => $user->id,
                        ])->create()->each(function ($goal) use ($user) {
                            TimeLog::factory(7)->state([
                                'goal_id' => $goal->id,
                                'user_id' => $user->id,
                            ])->create();
                        });
                    });
            });
    }
}
