<?php

namespace App\Http\Controllers\Api\V1;

use App\Data\Analysis\CompareItem;
use App\Data\Analysis\InfoItem;
use App\Http\Controllers\Controller;
use App\Services\AnalysisService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AnalysisController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, AnalysisService $analysisService)
    {
        $analysis = $analysisService->analyze($request->user());

        return response()->json([
            'weekSummary' => $analysis->weekSummary,
            'info' => Arr::map([
                'strongestGoal' => $analysis->strongestGoal,
                'weakestGoal' => $analysis->weakestGoal,
                'weeklyProgress' => $analysis->weeklyProgress,
                'weekOffer' => $analysis->weekOffer,
            ], fn (InfoItem $infoItem) => [
                'title' => $infoItem->title,
                'description' => $infoItem->description,
            ]),
            'compares' => $analysis->compares->map(fn (CompareItem $compareItem) => [
                'goalName' => $compareItem->goalName,
                'lastWeekPlannedMinutes' => $compareItem->lastWeekPlannedMinutes,
                'lastWeekSpentMinutes' => $compareItem->lastWeekSpentMinutes,
                'thisWeekPlannedMinutes' => $compareItem->thisWeekPlannedMinutes,
                'thisWeekSpentMinutes' => $compareItem->thisWeekSpentMinutes,
                'differenceMinutes' => $compareItem->differenceMinutes,
            ]),
        ], options: JSON_UNESCAPED_UNICODE);
    }
}
