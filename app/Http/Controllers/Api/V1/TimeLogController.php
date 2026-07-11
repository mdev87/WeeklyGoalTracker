<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTimeLogRequest;
use App\Http\Requests\UpdateTimeLogRequest;
use App\Http\Resources\TimeLogResource;
use App\Models\TimeLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class TimeLogController extends Controller
{
    /**
     * Display a listing of the resource for today.
     */
    public function today(Request $request)
    {
        /** @var User */
        $user = $request->user();

        $timeLogs = $user->timeLogs()
            ->where('date', jdate()->subDay()->format('Y-m-d'))
            ->with('goal:id,name,color')
            ->get();

        return response()->json([
            'count' => $timeLogs->count(),
            'data' => $timeLogs->toResourceCollection(TimeLogResource::class),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimeLogRequest $request)
    {
        Gate::authorize('create', [TimeLog::class, $request->safe()->input('goal_id')]);

        /** @var User */
        $user = $request->user();
        $timeLog = $user->timeLogs()
            ->create($request->validated())
            ->load('goal:id,name');

        return response()->json([
            'message' => 'Time log created successfully',
            'data' => $timeLog->toResource(TimeLogResource::class),
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeLogRequest $request, TimeLog $timeLog)
    {
        Gate::authorize('update', [$timeLog, $request->safe()->input('goal_id')]);
        $timeLog->update($request->validated());

        return response()->json([
            'message' => 'Time log updated successfully',
            'data' => $timeLog->load('goal:id,name')->toResource(),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeLog $timeLog)
    {
        Gate::authorize('delete', $timeLog);
        $timeLog->delete();

        return response(status: 204);
    }
}
