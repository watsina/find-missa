<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SchedulePeriodResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'start_date' => $this->start_date->toDateString(),
            'end_date' => $this->end_date->toDateString(),
            'formatted_date_range' => $this->formatted_date_range,
            'duration_in_days' => $this->duration_in_days,
            'is_active' => $this->is_active,
            'is_recurring' => $this->is_recurring,
            'is_universal' => $this->isUniversal(),
            'is_current' => $this->isCurrent(),
            'team_id' => $this->team_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
