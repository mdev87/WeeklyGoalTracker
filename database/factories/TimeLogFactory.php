<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Morilog\Jalali\Jalalian;

/**
 * @extends Factory<TimeLog>
 */
class TimeLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $userId = User::inRandomOrder()->first()->id;

        return [
            'duration_minutes' => fake()->numberBetween(5, 180),
            'date' => Jalalian::fromDateTime(
                fake()->dateTimeBetween('-6 days', 'now')
            )->format('Y-m-d'),
            'note' => fake()->optional()->randomElement([
                'امروز روی بخش اصلی پروژه کار کردم',
                'مطالعه و تمرین انجام شد',
                'بخش جدیدی یاد گرفتم',
                'تمرکز خوبی داشتم و جلو رفتم',
                'تمرین بیشتری نسبت به روز قبل انجام شد',
                'روی رفع اشکال وقت گذاشتم',
                'پیشرفت خوبی داشتم',
                'بخشی از کار هنوز کامل نشده',
                'تمرین امروز مفید بود',
                'روی جزئیات بیشتر کار کردم',
            ]),
            'goal_id' => Goal::where('user_id', $userId)->inRandomOrder()->first()->id,
            'user_id' => $userId,
        ];
    }
}
