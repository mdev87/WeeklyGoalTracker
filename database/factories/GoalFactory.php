<?php

namespace Database\Factories;

use App\Models\Goal;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Goal>
 */
class GoalFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->randomElement([
                'یادگیری عمیق لاراول',
                'ساخت وب‌سایت شخصی',
                'تقویت مهارت مکالمه انگلیسی',
                'تمام کردن یک کتاب تخصصی',
                'آماده شدن برای مصاحبه شغلی',
                'یادگیری طراحی پایگاه داده',
                'رسیدن به آمادگی جسمانی بهتر',
                'ساخت یک پروژه شخصی',
                'یادگیری مفاهیم سیستم دیزاین',
                'بهبود مهارت سخنرانی',
            ]),
            'color' => fake()->randomElement([
                'red',
                'blue',
                'green',
                'yellow',
                'purple',
                'pink',
                'indigo',
                'orange',
                'teal',
                'cyan',
            ]),
            'priority_percentage' => fake()->numberBetween(10, 100),
            'user_id' => User::inRandomOrder()->first()->id,
        ];
    }
}
