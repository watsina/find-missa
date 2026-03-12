<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use App\Enums\ActivityOccurrence;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityTimeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => $this->type,
            'activity_type_description' => $this->activity_type_description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'language' => $this->language,
            'church_id' => $this->church_id,
            'church' => new ChurchResource($this->whenLoaded('church')),
            'exceptions' => ActivityTimeExceptionResource::collection($this->whenLoaded('exceptions')),
            'occurrence' => $this->occurrence instanceof ActivityOccurrence
                ? $this->occurrence->value
                : $this->occurrence,
            'date' => $this->date,
            'day_of_week' => $this->day_of_week,
            'week_of_month' => $this->week_of_month,
            'week_of_month_label' => $this->week_of_month_label,
            'bi_weekly_start_date' => $this->bi_weekly_start_date?->toDateString(),
            'schedule_period_id' => $this->schedule_period_id,
            'schedule_period' => new SchedulePeriodResource($this->whenLoaded('schedulePeriod')),
            'effective_from' => $this->effective_from?->toDateString(),
            'effective_until' => $this->effective_until?->toDateString(),
        ];
    }
}
