<?php

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\UserActivityStreak;
use App\Services\UserStreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Morilog\Jalali\Jalalian;

uses(RefreshDatabase::class);

it('creates streak if user does not have one', function () {
    $user = User::factory()->create();

    app(UserStreakService::class)->incrementForUser($user);

    $this->assertDatabaseHas('user_activity_streaks', [
        'user_id' => $user->id,
    ]);
});

it('increments current streak if last active was yesterday', function () {
    $user = User::factory()->create();

    UserActivityStreak::create([
        'user_id' => $user->id,
        'current_streak' => 3,
        'longest_streak' => 5,
        'last_active' => Jalalian::now()->subDays(1)->format('Y-m-d'),
    ]);

    app(UserStreakService::class)->incrementForUser($user);

    $streak = $user->fresh()->streak;

    expect($streak->current_streak)
        ->toBe(4)
        ->and($streak->longest_streak)
        ->toBe(5)
        ->and($streak->last_active)
        ->toBe(jdate()->format('Y-m-d'));
});

it('updates longest streak when current streak exceeds it', function () {
    $user = User::factory()->create();

    UserActivityStreak::create([
        'user_id' => $user->id,
        'current_streak' => 5,
        'longest_streak' => 5,
        'last_active' => Jalalian::now()->subDays(1)->format('Y-m-d'),
    ]);

    app(UserStreakService::class)->incrementForUser($user);

    $streak = $user->fresh()->streak;

    expect($streak->current_streak)
        ->toBe(6)
        ->and($streak->longest_streak)
        ->toBe(6);
});

it('does not change longest streak if current streak is still lower', function () {
    $user = User::factory()->create();

    UserActivityStreak::create([
        'user_id' => $user->id,
        'current_streak' => 2,
        'longest_streak' => 10,
        'last_active' => Jalalian::now()->subDays(1)->format('Y-m-d'),
    ]);

    app(UserStreakService::class)->incrementForUser($user);

    $streak = $user->fresh()->streak;

    expect($streak->current_streak)
        ->toBe(3)
        ->and($streak->longest_streak)
        ->toBe(10);
});

it('does not increment streak if user already active today', function () {
    $user = User::factory()->create();

    UserActivityStreak::create([
        'user_id' => $user->id,
        'current_streak' => 8,
        'longest_streak' => 9,
        'last_active' => Jalalian::now()->format('Y-m-d'),
    ]);

    app(UserStreakService::class)->incrementForUser($user);

    $streak = $user->fresh()->streak;

    expect($streak->current_streak)
        ->toBe(8)
        ->and($streak->longest_streak)
        ->toBe(9);
});

it('resets streak if last activity was before yesterday', function () {
    $user = User::factory()->create();

    UserActivityStreak::create([
        'user_id' => $user->id,
        'current_streak' => 12,
        'longest_streak' => 15,
        'last_active' => Jalalian::now()->subDays(3)->format('Y-m-d'),
    ]);

    app(UserStreakService::class)->incrementForUser($user);

    $streak = $user->fresh()->streak;

    expect($streak->current_streak)
        ->toBe(1)
        ->and($streak->longest_streak)
        ->toBe(15)
        ->and($streak->last_active)
        ->toBe(jdate()->format('Y-m-d'));
});

it('does not create duplicate streak record', function () {
    $user = User::factory()->create();

    UserActivityStreak::create([
        'user_id' => $user->id,
        'current_streak' => 1,
        'longest_streak' => 1,
        'last_active' => Jalalian::now()->format('Y-m-d'),
    ]);

    app(UserStreakService::class)->incrementForUser($user);

    expect(UserActivityStreak::where('user_id', $user->id)->count())
        ->toBe(1);
});

it('returns null when there are no timelogs this week', function () {
    $user = User::factory()->create();

    expect(app(UserStreakService::class)->mostActiveWeekDay($user))
        ->toBeNull();
});

it('returns most active weekday', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create();

    $saturday = Jalalian::now()->getFirstDayOfWeek();

    TimeLog::factory()->create([
        'user_id' => $user->id,
        'date' => $saturday->format('Y-m-d'),
        'goal_id' => $goal->id,
    ]);

    TimeLog::factory()->create([
        'user_id' => $user->id,
        'date' => $saturday->format('Y-m-d'),
        'goal_id' => $goal->id,
    ]);

    TimeLog::factory()->create([
        'user_id' => $user->id,
        'date' => $saturday->addDay()->format('Y-m-d'),
        'goal_id' => $goal->id,
    ]);

    expect(app(UserStreakService::class)->mostActiveWeekDay($user))
        ->toBe('sat');
});

it('ignores timelogs outside current week', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create();

    TimeLog::factory()->create([
        'date' => jdate()->subDays(15)->format('Y-m-d'),
        'user_id' => $user->id,
        'goal_id' => $goal->id,
    ]);

    expect(app(UserStreakService::class)->mostActiveWeekDay($user))
        ->toBeNull();
});
