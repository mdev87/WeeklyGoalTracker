<?php

use App\Models\Goal;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();

    $this->goal = Goal::factory()->create([
        'user_id' => $this->user->id,
    ]);
});

describe('GET /api/v1/time-logs/today', function () {

    it('returns yesterday time logs for authenticated user', function () {
        TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'duration_minutes' => 60,
            'date' => jdate()->subDay()->format('Y-m-d'),
            'note' => 'Worked',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/time-logs/today');

        $response
            ->assertOk()
            ->assertJson([
                'count' => 1,
            ])
            ->assertJsonStructure([
                'count',
                'data' => [
                    '*' => [
                        'id',
                        'durationMinutes',
                        'date',
                        'note',
                        'goal' => [
                            'id',
                            'name',
                            'color',
                        ],
                    ],
                ],
            ]);
    });

    it('returns only authenticated user time logs', function () {
        $otherUser = User::factory()->create();

        $otherGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'date' => jdate()->subDay()->format('Y-m-d'),
        ]);

        TimeLog::factory()->create([
            'user_id' => $otherUser->id,
            'goal_id' => $otherGoal->id,
            'date' => jdate()->subDay()->format('Y-m-d'),
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->getJson('/api/v1/time-logs/today');

        $response->assertOk();

        expect($response->json('count'))->toBe(1);

        expect($response->json('data.0.goal.id'))
            ->toBe($this->goal->id);
    });

    it('does not return logs from other dates', function () {
        TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'date' => jdate()->format('Y-m-d'),
        ]);

        TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'date' => jdate()->subDays(2)->format('Y-m-d'),
        ]);

        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/time-logs/today')
            ->assertOk()
            ->assertJson([
                'count' => 0,
                'data' => [],
            ]);
    });

    it('returns an empty collection when user has no logs', function () {
        Sanctum::actingAs($this->user);

        $this->getJson('/api/v1/time-logs/today')
            ->assertOk()
            ->assertExactJson([
                'count' => 0,
                'data' => [],
            ]);
    });
});

describe('POST /api/v1/time-logs', function () {

    it('creates a new time log', function () {
        Sanctum::actingAs($this->user);

        $payload = [
            'goal_id' => $this->goal->id,
            'duration_minutes' => 90,
            'date' => jdate()->subDay()->format('Y-m-d'),
            'note' => 'Deep work',
        ];

        $response = $this->postJson('/api/v1/time-logs', $payload);

        $response
            ->assertCreated()
            ->assertJson([
                'message' => 'Time log created successfully',
            ]);

        $this->assertDatabaseHas('time_logs', [
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'duration_minutes' => 90,
            'date' => jdate()->subDay()->format('Y-m-d'),
            'note' => 'Deep work',
        ]);
    });

    it('always stores authenticated user as owner', function () {
        $otherUser = User::factory()->create();

        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/time-logs', [
            'goal_id' => $this->goal->id,
            'duration_minutes' => 30,
            'date' => jdate()->subDay()->format('Y-m-d'),

            // malicious payload
            'user_id' => $otherUser->id,
        ])->assertCreated();

        $this->assertDatabaseHas('time_logs', [
            'user_id' => $this->user->id,
        ]);

        $this->assertDatabaseMissing('time_logs', [
            'user_id' => $otherUser->id,
        ]);
    });

    it('validates duration_minutes', function () {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/time-logs', [
            'goal_id' => $this->goal->id,
            'duration_minutes' => 0,
            'date' => jdate()->subDay()->format('Y-m-d'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('duration_minutes');
    });

    it('rejects future dates', function () {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/time-logs', [
            'goal_id' => $this->goal->id,
            'duration_minutes' => 30,
            'date' => jdate()->addDay()->format('Y-m-d'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    });

    it('validates goal exists', function () {
        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/time-logs', [
            'goal_id' => 999999,
            'duration_minutes' => 30,
            'date' => jdate()->subDay()->format('Y-m-d'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('goal_id');
    });
});

describe('PATCH /api/v1/time-logs/{timeLog}', function () {

    it('updates a time log', function () {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'duration_minutes' => 60,
            'date' => jdate()->subDay()->format('Y-m-d'),
            'note' => 'Old note',
        ]);

        Sanctum::actingAs($this->user);

        $response = $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'duration_minutes' => 120,
            'note' => 'Updated note',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'message' => 'Time log updated successfully',
            ]);

        $this->assertDatabaseHas('time_logs', [
            'id' => $timeLog->id,
            'duration_minutes' => 120,
            'note' => 'Updated note',
        ]);
    });

    it('supports partial updates', function () {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
            'duration_minutes' => 45,
            'date' => jdate()->subDay()->format('Y-m-d'),
            'note' => 'Keep this note',
        ]);

        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'duration_minutes' => 90,
        ])->assertOk();

        $this->assertDatabaseHas('time_logs', [
            'id' => $timeLog->id,
            'duration_minutes' => 90,
            'note' => 'Keep this note',
        ]);
    });

    it('validates duration_minutes', function () {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'duration_minutes' => 0,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('duration_minutes');
    });

    it('rejects future dates', function () {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'date' => jdate()->addDay()->format('Y-m-d'),
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('date');
    });

    it('ignores forged user_id', function () {
        $otherUser = User::factory()->create();

        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'user_id' => $otherUser->id,
        ])->assertOk();

        expect($timeLog->fresh()->user_id)
            ->toBe($this->user->id);
    });

    it('cannot update another users time log', function () {
        $otherUser = User::factory()->create();

        $otherGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $timeLog = TimeLog::factory()->create([
            'user_id' => $otherUser->id,
            'goal_id' => $otherGoal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'duration_minutes' => 999,
        ])->assertForbidden();

        expect($timeLog->fresh()->duration_minutes)
            ->not()->toBe(999);
    });

    it('returns 404 when time log does not exist', function () {
        Sanctum::actingAs($this->user);

        $this->patchJson('/api/v1/time-logs/999999', [
            'duration_minutes' => 60,
        ])->assertNotFound();
    });
});

describe('DELETE /api/v1/time-logs/{timeLog}', function () {

    it('deletes own time log', function () {
        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $this->goal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->deleteJson("/api/v1/time-logs/{$timeLog->id}")
            ->assertNoContent();

        $this->assertDatabaseMissing('time_logs', [
            'id' => $timeLog->id,
        ]);
    });

    it('cannot delete another users time log', function () {
        $otherUser = User::factory()->create();

        $otherGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $timeLog = TimeLog::factory()->create([
            'user_id' => $otherUser->id,
            'goal_id' => $otherGoal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->deleteJson("/api/v1/time-logs/{$timeLog->id}")
            ->assertForbidden();

        $this->assertDatabaseHas('time_logs', [
            'id' => $timeLog->id,
        ]);
    });

    it('returns 404 when time log does not exist', function () {
        Sanctum::actingAs($this->user);

        $this->deleteJson('/api/v1/time-logs/999999')
            ->assertNotFound();
    });
});

describe('Security', function () {

    it('cannot create a time log for another users goal', function () {
        $otherUser = User::factory()->create();

        $otherGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->postJson('/api/v1/time-logs', [
            'goal_id' => $otherGoal->id,
            'duration_minutes' => 60,
            'date' => jdate()->subDay()->format('Y-m-d'),
        ])->assertForbidden();
    });

    it('cannot update a time log for another users goal', function () {
        $otherUser = User::factory()->create();

        $otherGoal = Goal::factory()->create([
            'user_id' => $otherUser->id,
        ]);

        $ownGoal = Goal::factory()->create([
            'user_id' => $this->user->id,
        ]);

        $timeLog = TimeLog::factory()->create([
            'user_id' => $this->user->id,
            'goal_id' => $ownGoal->id,
        ]);

        Sanctum::actingAs($this->user);

        $this->patchJson("/api/v1/time-logs/{$timeLog->id}", [
            'goal_id' => $otherGoal->id,
            'duration_minutes' => 60,
        ])->assertForbidden();
    });
});
