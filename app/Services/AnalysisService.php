<?php

namespace App\Services;

use App\Data\Analysis\AnalysisData;
use App\Data\Analysis\CompareItem;
use App\Data\Analysis\InfoItem;

class AnalysisService
{
    public function analyze()
    {
        // TODO: Implementing the logic of this service
        return new AnalysisData(
            weekSummary: '',
            date: 'date',
            strongestGoal: new InfoItem('', ''),
            weakestGoal: new InfoItem('', ''),
            weekOffer: new InfoItem('', ''),
            weeklyProgress: new InfoItem('', ''),
            compares: collect([
                new CompareItem(
                    goalName: '',
                    lastWeekPlannedMinutes: 1,
                    lastWeekSpentMinutes: 1,
                    thisWeekPlannedMinutes: 1,
                    thisWeekSpentMinutes: 1,
                    differenceMinutes: 1
                ),
            ])
        );
    }
}
