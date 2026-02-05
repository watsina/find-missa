<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiturgicalScheduleResource extends JsonResource
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
            'church_id' => $this->church_id,
            'church' => new ChurchResource($this->whenLoaded('church')),
            'holiday_id' => $this->holiday_id,
            'holiday' => new HolidayResource($this->whenLoaded('holiday')),
            'year' => $this->year,
            'replaces_regular_schedule' => $this->replaces_regular_schedule,
            'notes' => $this->notes,
            'times' => LiturgicalScheduleTimeResource::collection($this->whenLoaded('times')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
