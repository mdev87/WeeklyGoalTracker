<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\ActivityOverview\DayLog;
use App\Http\Controllers\Controller;
use App\Services\ActivityOverviewService;
use Illuminate\Http\Request;

class ActivityOverviewController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ActivityOverviewService $overviewService)
    {
        $overviewData = $overviewService->getOverview($request->user());

        return [
            'totalWeeksThisYear' => $overviewData->totalWeeksThisYear,
            'longestStreak' => $overviewData->longestStreak,
            'currentStreak' => $overviewData->currentStreak,
            'mostActiveWeekDay' => $overviewData->mostActiveWeekDay,
            'activityLogsThisYear' => $overviewData->activityLogsThisYear->map(fn (DayLog $log) => [
                'level' => $log->level,
                'count' => $log->count,
                'date' => $log->date,
            ]),
        ];
    }
}
