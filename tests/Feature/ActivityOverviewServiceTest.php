<?php

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use App\Repositories\TimeLogRepository;
use App\Repositories\UserStreakRepository;
use App\Repositories\WeekRepository;
use App\Services\ActivityOverviewService;
use App\Services\UserStreakService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {

    $this->user = User::factory()->create();

    $streak = $this->user->streak()->create([
        'current_streak' => 4,
        'longest_streak' => 12,
        'last_active' => jdate()->format('Y-m-d'),
    ]);

    $this->mock(UserStreakRepository::class)
        ->shouldReceive('getStreak')
        ->once()
        ->andReturn($streak);

    $this->mock(UserStreakService::class)
        ->shouldReceive('mostActiveWeekDay')
        ->once()
        ->andReturn('sat');

    $this->mock(WeekRepository::class)
        ->shouldReceive('getWeeksCountThisYear')
        ->once()
        ->with($this->user)
        ->andReturn(15);
});

it('returns overview data', function () {
    $logs = collect([
        TimeLog::make(['date' => '1405-01-01'])
            ->setAttribute('count', 1),
    ]);

    $this->mock(TimeLogRepository::class)
        ->shouldReceive('getActivityLogsThisYear')
        ->once()
        ->andReturn($logs);

    $overview = app(ActivityOverviewService::class)->getOverview($this->user);

    expect($overview->totalWeeksThisYear)->toBe(15)
        ->and($overview->currentStreak)->toBe(4)
        ->and($overview->longestStreak)->toBe(12)
        ->and($overview->mostActiveWeekDay)->toBe('sat')
        ->and($overview->activityLogsThisYear)->toHaveCount(1);
});

it('assigns level 0 when count is zero', function () {
    $log = TimeLog::factory()->make([
        'date' => jdate()->format('Y-m-d'),
        'user_id' => $this->user->id,
        'goal_id' => Goal::factory()->create()->id,
    ])->setAttribute('count', 0);

    $this->mock(TimeLogRepository::class)
        ->shouldReceive('getActivityLogsThisYear')
        ->once()
        ->andReturn(collect([$log]));

    $overview = app(ActivityOverviewService::class)->getOverview($this->user);

    expect($overview->activityLogsThisYear)
        ->toHaveCount(1)
        ->and($overview->activityLogsThisYear->first()->level)
        ->toBe(0);
});

it('assigns levels correctly for four activity counts', function () {
    $logs = collect();

    foreach ([1, 2, 3, 4] as $index => $count) {
        $log = TimeLog::factory()->create([
            'date' => '1405-01-0'.($index + 1),
            'user_id' => $this->user->id,
            'goal_id' => Goal::factory()->create()->id,
        ])->setAttribute('count', $count);

        $logs->push($log);
    }

    $this->mock(TimeLogRepository::class)
        ->shouldReceive('getActivityLogsThisYear')
        ->once()
        ->with($this->user)
        ->andReturn($logs);

    $levels = app(ActivityOverviewService::class)
        ->getOverview($this->user)
        ->activityLogsThisYear
        ->pluck('level');

    expect($levels->all())->toBe([1, 2, 3, 4]);
});

it('assigns level 3 correctly', function () {
    $logs = collect();

    foreach ([1, 2, 3, 4, 5] as $index => $count) {
        $log = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'date' => '1405-01-0'.($index + 1),
            'goal_id' => Goal::factory()->create()->id,
        ])->setAttribute('count', $count);

        $logs->push($log);
    }

    $this->mock(TimeLogRepository::class)
        ->shouldReceive('getActivityLogsThisYear')
        ->andReturn($logs);

    $levels = app(ActivityOverviewService::class)
        ->getOverview($this->user)
        ->activityLogsThisYear
        ->pluck('level');

    expect($levels->all())->toBe([1, 1, 2, 3, 4]);
});

it('assigns level 4 to counts above third quartile', function () {
    $logs = collect();

    foreach ([2, 4, 6, 8, 10, 12, 14, 16] as $index => $count) {
        $log = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'date' => '1405-01-0'.($index + 1),
            'goal_id' => Goal::factory()->create()->id,
        ])->setAttribute('count', $count);

        $logs->push($log);
    }

    $this->mock(TimeLogRepository::class)
        ->shouldReceive('getActivityLogsThisYear')
        ->andReturn($logs);

    $levels = app(ActivityOverviewService::class)
        ->getOverview($this->user)
        ->activityLogsThisYear
        ->pluck('level');

    expect($levels->last())->toBe(4);
});

it('preserves date and count in generated day logs', function () {
    $log = TimeLog::factory()->create([
        'user_id' => $this->user->id,
        'date' => '1405-02-10',
        'goal_id' => Goal::factory()->create()->id,
    ])->setAttribute('count', 7);

    $this->mock(TimeLogRepository::class)
        ->shouldReceive('getActivityLogsThisYear')
        ->andReturn(collect([$log]));

    $dayLog = app(ActivityOverviewService::class)
        ->getOverview($this->user)
        ->activityLogsThisYear
        ->first();

    expect($dayLog->count)
        ->toBe(7)
        ->and($dayLog->date)
        ->toBe('1405-02-10');
});
