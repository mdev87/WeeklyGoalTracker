<?php

use App\Models\User;
use App\Models\Week;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->user = User::factory()->create();

    Sanctum::actingAs($this->user);
});

describe('PATCH /api/v1/weeks/current', function () {

    it('creates current week when it does not exist', function () {

        $this->patchJson('/api/v1/weeks/current', [
            'planned_minutes' => 1800,
        ])
            ->assertOk()
            ->assertJson([
                'message' => 'Week updated successfully',
            ]);

        $this->assertDatabaseHas('weeks', [
            'user_id' => $this->user->id,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
            'planned_minutes' => 1800,
        ]);
    });

    it('updates current week when it already exists', function () {

        $week = Week::factory()->create([
            'user_id' => $this->user->id,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
            'planned_minutes' => 1200,
        ]);

        $this->patchJson('/api/v1/weeks/current', [
            'planned_minutes' => 2400,
        ])
            ->assertOk();

        expect($week->fresh()->planned_minutes)
            ->toBe(2400);
    });

    it('only updates authenticated users current week', function () {

        $otherUser = User::factory()->create();

        Week::factory()->create([
            'user_id' => $otherUser->id,
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
            'planned_minutes' => 900,
        ]);

        $this->patchJson('/api/v1/weeks/current', [
            'planned_minutes' => 2000,
        ])->assertOk();

        $this->assertDatabaseHas('weeks', [
            'user_id' => $otherUser->id,
            'planned_minutes' => 900,
        ]);

        $this->assertDatabaseHas('weeks', [
            'user_id' => $this->user->id,
            'planned_minutes' => 2000,
        ]);
    });

    it('validates planned minutes is required', function () {

        $this->patchJson('/api/v1/weeks/current', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('planned_minutes');
    });

    it('validates planned minutes is an integer', function () {

        $this->patchJson('/api/v1/weeks/current', [
            'planned_minutes' => 'invalid',
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('planned_minutes');
    });

    it('validates planned minutes minimum value', function () {

        $this->patchJson('/api/v1/weeks/current', [
            'planned_minutes' => 29,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('planned_minutes');
    });

    it('validates planned minutes maximum value', function () {

        $this->patchJson('/api/v1/weeks/current', [
            'planned_minutes' => 8001,
        ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('planned_minutes');
    });
});
