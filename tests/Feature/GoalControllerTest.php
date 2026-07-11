<?php

use App\Models\Goal;
use App\Models\User;
use App\Models\Week;
use App\Repositories\TimeLogRepository;
use App\Repositories\WeekRepository;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();

    Sanctum::actingAs($this->user);
});

describe('GET /api/v1/goals', function () {

    it('returns only authenticated user goals', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'priority_percentage' => 40,
        ]);

        Goal::factory()->create();

        $week = new Week([
            'planned_minutes' => 600,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        ]);

        $this->mock(WeekRepository::class)
            ->shouldReceive('getCurrentWeek')
            ->once()
            ->andReturn($week);

        $this->mock(TimeLogRepository::class)
            ->shouldReceive('getWeekLogs')
            ->once()
            ->andReturn(collect());

        $response = $this->getJson('/api/v1/goals');

        $response
            ->assertOk()
            ->assertJson([
                'count' => 1,
            ]);

        expect($response->json('data.0.id'))
            ->toBe($goal->id);
    });

    it('returns calculated statistics', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'priority_percentage' => 25,
        ]);

        $week = new Week([
            'planned_minutes' => 600,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        ]);

        $logs = collect([
            (object) [
                'goal_id' => $goal->id,
                'duration_minutes' => 40,
            ],
            (object) [
                'goal_id' => $goal->id,
                'duration_minutes' => 20,
            ],
        ]);

        $this->mock(WeekRepository::class)
            ->shouldReceive('getCurrentWeek')
            ->andReturn($week);

        $this->mock(TimeLogRepository::class)
            ->shouldReceive('getWeekLogs')
            ->andReturn($logs);

        $response = $this->getJson('/api/v1/goals');

        $response->assertOk();

        expect($response->json('data.0.plannedMinutes'))
            ->toBe(150);

        expect($response->json('data.0.spentMinutes'))
            ->toBe(60);

        expect($response->json('data.0.remainingMinutes'))
            ->toBe(90);

        expect($response->json('data.0.completionPercentage'))
            ->toBe(40);
    });

    it('returns empty collection when user has no goals', function () {

        $week = new Week([
            'planned_minutes' => 600,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        ]);

        $this->mock(WeekRepository::class)
            ->shouldReceive('getCurrentWeek')
            ->andReturn($week);

        $this->mock(TimeLogRepository::class)
            ->shouldReceive('getWeekLogs')
            ->andReturn(collect());

        $this->getJson('/api/v1/goals')
            ->assertOk()
            ->assertExactJson([
                'count' => 0,
                'data' => [],
            ]);
    });
});

describe('POST /api/v1/goals', function () {

    it('creates a goal', function () {

        $payload = [
            'name' => 'Learn Laravel',
            'color' => 'blue',
            'priority_percentage' => 40,
        ];

        $this->postJson('/api/v1/goals', $payload)
            ->assertCreated()
            ->assertJson([
                'message' => 'Goal created successfully',
            ]);

        $this->assertDatabaseHas('goals', [
            'user_id' => $this->user->id,
            'name' => 'Learn Laravel',
            'color' => 'blue',
            'priority_percentage' => 40,
        ]);
    });

    it('always stores authenticated user as owner', function () {

        $otherUser = User::factory()->create();

        $this->postJson('/api/v1/goals', [
            'name' => 'Goal',
            'color' => 'blue',
            'priority_percentage' => 20,

            // malicious payload
            'user_id' => $otherUser->id,
        ])->assertCreated();

        $this->assertDatabaseHas('goals', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('goals', [
            'user_id' => $otherUser->id,
        ]);
    });

    it('validates remaining priority percentage', function () {

        Goal::factory()->create([
            'user_id' => $this->user->id,
            'priority_percentage' => 90,
        ]);

        $this->postJson('/api/v1/goals', [
            'name' => 'Another goal',
            'color' => 'blue',
            'priority_percentage' => 20,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('priority_percentage');
    });
});

describe('PATCH /api/v1/goals/{goal}', function () {

    it('updates a goal', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Old name',
            'color' => 'blue',
            'priority_percentage' => 30,
        ]);

        $this->patchJson("/api/v1/goals/{$goal->id}", [
            'name' => 'New name',
            'color' => 'green',
            'priority_percentage' => 25,
        ])
            ->assertOk()
            ->assertJson([
                'message' => 'Goal updated successfully',
            ]);

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'name' => 'New name',
            'color' => 'green',
            'priority_percentage' => 25,
        ]);
    });

    it('supports partial updates', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
            'name' => 'Laravel',
            'color' => 'blue',
            'priority_percentage' => 40,
        ]);

        $this->patchJson("/api/v1/goals/{$goal->id}", [
            'name' => 'Laravel 12',
        ])->assertOk();

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
            'name' => 'Laravel 12',
            'color' => 'blue',
            'priority_percentage' => 40,
        ]);
    });

    it('cannot update another users goal', function () {

        $otherGoal = Goal::factory()->create();

        $this->patchJson("/api/v1/goals/{$otherGoal->id}", [
            'name' => 'Hacked',
        ])->assertForbidden();

        $this->assertDatabaseMissing('goals', [
            'id' => $otherGoal->id,
            'name' => 'Hacked',
        ]);
    });

    it('returns 404 for missing goal', function () {

        $this->patchJson('/api/v1/goals/999999', [
            'name' => 'Anything',
        ])->assertNotFound();
    });

    it('validates required fields when present', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/goals/{$goal->id}", [
            'name' => '',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('name');
    });

    it('validates invalid goal color', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->patchJson("/api/v1/goals/{$goal->id}", [
            'color' => 'pink',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('color');
    });
});

describe('DELETE /api/v1/goals/{goal}', function () {

    it('deletes a goal', function () {

        $goal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $this->deleteJson("/api/v1/goals/{$goal->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('goals', [
            'id' => $goal->id,
        ]);
    });

    it('cannot delete another users goal', function () {

        $goal = Goal::factory()->create();

        $this->deleteJson("/api/v1/goals/{$goal->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('goals', [
            'id' => $goal->id,
        ]);
    });

    it('returns 404 for missing goal', function () {

        $this->deleteJson('/api/v1/goals/999999')
            ->assertNotFound();
    });
});
