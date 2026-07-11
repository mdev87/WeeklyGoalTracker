<?php

namespace App\Http\Resources;

use App\Models\TimeLog;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @see TimeLog */
class TimeLogResource extends JsonResource
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
            'durationMinutes' => $this->whenHas('duration_minutes'),
            'date' => $this->whenHas('date'),
            'note' => $this->whenNotNull($this->note),
            'goal' => GoalResource::make($this->whenLoaded('goal')),
        ];
    }
}
