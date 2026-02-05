<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdorationChapelResource extends JsonResource
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
            'name' => $this->name,
            'notes' => $this->notes,
            'schedules' => AdorationChapelScheduleResource::collection($this->whenLoaded('schedules')),
            'exceptions' => AdorationChapelExceptionResource::collection($this->whenLoaded('exceptions')),
            'formatted_hours' => $this->when(
                $this->relationLoaded('schedules'),
                fn () => $this->getFormattedHours()
            ),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
