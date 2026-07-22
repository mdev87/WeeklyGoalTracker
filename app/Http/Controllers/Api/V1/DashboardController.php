<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Dashboard\GoalStatsData;
use App\Data\Dashboard\TimeLogData;
use App\Http\Controllers\Controller;
use App\Services\DashboardService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, DashboardService $dashboardService)
    {
        $data = $dashboardService->get($request->user());
        $weeklyStats = $data->weeklyStats;
        $goalStats = $data->weeklyStats->goalStats;
        $todayLogs = $data->weeklyStats->todayLogs;

        return [
            'activeDays' => $data->activeStreakDays,
            'weeklyStats' => [
                'plannedMinutes' => $weeklyStats->plannedMinutes,
                'spentMinutes' => $weeklyStats->spentMinutes,
                'remainingMinutes' => $weeklyStats->remainingMinutes,
                'completionPercentage' => $weeklyStats->completionPercentage,
                'goals' => [
                    'count' => $goalStats->count(),
                    'data' => $weeklyStats->goalStats->map(fn (GoalStatsData $g) => [
                        'name' => $g->goal->name,
                        'color' => $g->goal->color,
                        'plannedMinutes' => $g->plannedMinutes,
                        'spentMinutes' => $g->spentMinutes,
                        'remainingMinutes' => $g->remainingMinutes,
                        'completionPercentage' => $g->completionPercentage,
                    ]),
                ],
                'todayLogs' => $todayLogs->map(fn (TimeLogData $t) => [
                    'id' => $t->id,
                    'durationMinutes' => $t->durationMinutes,
                    'goal' => [
                        'id' => $t->goal->id,
                        'name' => $t->goal->name,
                        'color' => $t->goal->color,
                    ],
                ]),
            ],
        ];
    }
}
