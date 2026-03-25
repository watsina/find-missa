<?php

namespace App\Http\Resources;

use App\Enums\DayPattern;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdorationChapelScheduleResource extends JsonResource
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
            'adoration_chapel_id' => $this->adoration_chapel_id,
            'day_pattern' => $this->day_pattern instanceof DayPattern
                ? $this->day_pattern->value
                : $this->day_pattern,
            'days_of_week' => $this->days_of_week,
            'opens_at' => $this->opens_at,
            'closes_at' => $this->closes_at,
            'is_open_24_hours' => $this->is_open_24_hours,
            'is_closed' => $this->is_closed,
            'day_details' => AdorationChapelScheduleDetailResource::collection($this->whenLoaded('dayDetails')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
