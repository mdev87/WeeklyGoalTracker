<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class WeekController extends Controller
{
    public function updateCurrent(Request $request)
    {
        $validated = $request->validate([
            'planned_minutes' => 'required|integer|min:30|max:8000',
        ]);

        /** @var User */
        $user = $request->user();

        $user->weeks()->updateOrCreate([
            'week_start_date' => jdate()->getFirstDayOfWeek()->format('Y-m-d'),
        ], $validated);

        return response()->json([
            'message' => 'Week updated successfully',
        ]);
    }
}
