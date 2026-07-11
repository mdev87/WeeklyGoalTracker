<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreGoalRequest;
use App\Http\Requests\UpdateGoalRequest;
use App\Http\Resources\GoalResource;
use App\Models\Goal;
use App\Models\User;
use App\Repositories\TimeLogRepository;
use App\Repositories\WeekRepository;
use App\Services\WeeklyStatsService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Morilog\Jalali\Jalalian;

class GoalController extends Controller
{
    public function __construct(
        protected WeeklyStatsService $weeklyStatsService,
        protected WeekRepository $weekRepository,
        protected TimeLogRepository $timeLogRepository
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $goals = Goal::whereUserId(Auth::id())->get();
        $thisWeek = $this->weekRepository
            ->getCurrentWeek(Auth::user(), config('week.default_planned_minutes'));
        $startDate = $thisWeek->week_start_date;
        $endDate = Jalalian::fromFormat('Y-m-d', $thisWeek->week_start_date)
            ->getEndDayOfWeek()->format('Y-m-d');
        $timeLogs = $this->timeLogRepository->getWeekLogs(Auth::user(), $startDate, $endDate);

        $goals = $goals->map(function (Goal $goal) use ($thisWeek, $timeLogs) {
            $goal->planned_minutes = $goal->priority_percentage * $thisWeek->planned_minutes / 100;
            $goal->spent_minutes = $timeLogs->where('goal_id', $goal->id)->sum('duration_minutes');
            $goal->remaining_minutes = $goal->planned_minutes - $goal->spent_minutes;
            $goal->completion_percentage = $goal->planned_minutes > 0 ? (100 * $goal->spent_minutes / $goal->planned_minutes) : 0;

            return $goal;
        });

        return response()->json([
            'count' => $goals->count(),
            'data' => $goals->toResourceCollection(GoalResource::class),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreGoalRequest $request)
    {
        /** @var User */
        $user = $request->user();
        $user->goals()->create($request->validated());

        return response()->json([
            'message' => 'Goal created successfully',
        ], 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateGoalRequest $request, Goal $goal)
    {
        Gate::authorize('update', $goal);
        $goal->update($request->validated());

        return response()->json([
            'message' => 'Goal updated successfully',
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Goal $goal)
    {
        Gate::authorize('delete', $goal);
        $goal->delete();

        return response(status: 204);
    }
}
