<?php

namespace App\Http\Resources;

use App\Enums\DayPattern;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityScheduleResource extends JsonResource
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
            'type' => $this->type,
            'activity_type_description' => $this->activity_type_description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'language' => $this->language,
            'day_pattern' => $this->day_pattern instanceof DayPattern
                ? $this->day_pattern->value
                : $this->day_pattern,
            'days_of_week' => $this->days_of_week,
            'week_of_month' => $this->week_of_month,
            'week_of_month_label' => $this->week_of_month_label,
            'day_of_week' => $this->day_of_week,
            'has_day_variations' => $this->has_day_variations,
            'schedule_period_id' => $this->schedule_period_id,
            'schedule_period' => new SchedulePeriodResource($this->whenLoaded('schedulePeriod')),
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_until' => $this->effective_until?->toDateString(),
            'day_details' => ScheduleDayDetailResource::collection($this->whenLoaded('dayDetails')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
