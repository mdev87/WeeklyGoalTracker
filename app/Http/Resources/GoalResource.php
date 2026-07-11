<?php

namespace App\Http\Resources;

use App\Models\Goal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @see Goal */
class GoalResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->whenHas('name'),
            'color' => $this->whenHas('color'),
            'priorityPercentage' => $this->whenHas('priority_percentage'),
            'plannedMinutes' => $this->whenHas('planned_minutes'),
            'spentMinutes' => $this->whenHas('spent_minutes'),
            'remainingMinutes' => $this->whenHas('remaining_minutes'),
            'completionPercentage' => $this->whenHas('completion_percentage'),
        ];
    }
}
