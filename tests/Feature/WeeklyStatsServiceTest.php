<?php

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use App\Models\Week;
use App\Services\WeeklyStatsService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('calculates weekly stats correctly', function () {

    $user = User::factory()->create();

    Week::create([
        'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        'planned_minutes' => 600,
        'user_id' => $user->id,
    ]);

    $goal = Goal::create([
        'name' => 'Reading',
        'color' => 'blue',
        'priority_percentage' => 100,
        'user_id' => $user->id,
    ]);

    TimeLog::create([
        'duration_minutes' => 180,
        'date' => jdate()->format('Y-m-d'),
        'user_id' => $user->id,
        'goal_id' => $goal->id,
    ]);

    $stats = app(WeeklyStatsService::class)->getStats($user);

    expect($stats->plannedMinutes)->toBe(600)
        ->and($stats->spentMinutes)->toBe(180)
        ->and($stats->remainingMinutes)->toBe(420)
        ->and($stats->completionPercentage)->toBe(30.0);
});

it('returns zero completion when planned minutes are zero', function () {

    $user = User::factory()->create();

    Week::create([
        'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        'planned_minutes' => 0,
        'user_id' => $user->id,
    ]);

    $stats = app(WeeklyStatsService::class)->getStats($user);

    expect($stats->completionPercentage)->toBe(0.0)
        ->and($stats->remainingMinutes)->toBe(0);
});

it('calculates goal stats correctly', function () {

    $user = User::factory()->create();

    Week::create([
        'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        'planned_minutes' => 1000,
        'user_id' => $user->id,
    ]);

    $goal = Goal::create([
        'name' => 'Coding',
        'color' => 'blue',
        'priority_percentage' => 25,
        'user_id' => $user->id,
    ]);

    TimeLog::create([
        'duration_minutes' => 100,
        'date' => jdate()->format('Y-m-d'),
        'user_id' => $user->id,
        'goal_id' => $goal->id,
    ]);

    $stats = app(WeeklyStatsService::class)->getStats($user);

    $goalStats = $stats->goalStats->first();

    expect($goalStats->plannedMinutes)->toBe(250)
        ->and($goalStats->spentMinutes)->toBe(100)
        ->and($goalStats->remainingMinutes)->toBe(150)
        ->and($goalStats->completionPercentage)->toBe(40.0);
});

it('returns only today logs', function () {

    $user = User::factory()->create();

    Week::create([
        'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        'planned_minutes' => 500,
        'user_id' => $user->id,
    ]);

    $goal = Goal::create([
        'name' => 'Coding',
        'color' => 'blue',
        'priority_percentage' => 100,
        'user_id' => $user->id,
    ]);

    $today = TimeLog::create([
        'duration_minutes' => 60,
        'date' => jdate()->format('Y-m-d'),
        'user_id' => $user->id,
        'goal_id' => $goal->id,
    ]);

    TimeLog::create([
        'duration_minutes' => 40,
        'date' => jdate()->subDay()->format('Y-m-d'),
        'user_id' => $user->id,
        'goal_id' => $goal->id,
    ]);

    $stats = app(WeeklyStatsService::class)->getStats($user);

    expect($stats->todayLogs)->toHaveCount(1)
        ->and($stats->todayLogs->first()->id)->toBe($today->id);
});

it('supports overspent planned minutes', function () {

    $user = User::factory()->create();

    Week::create([
        'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        'planned_minutes' => 100,
        'user_id' => $user->id,
    ]);

    $goal = Goal::create([
        'name' => 'Coding',
        'color' => 'blue',
        'priority_percentage' => 100,
        'user_id' => $user->id,
    ]);

    TimeLog::create([
        'duration_minutes' => 180,
        'date' => jdate()->format('Y-m-d'),
        'user_id' => $user->id,
        'goal_id' => $goal->id,
    ]);

    $stats = app(WeeklyStatsService::class)->getStats($user);

    expect($stats->remainingMinutes)->toBe(-80)
        ->and($stats->completionPercentage)->toBe(180.0);
});
