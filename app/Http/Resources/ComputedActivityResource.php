<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for ComputedActivity DTO.
 * Used to transform computed activities for API responses.
 */
class ComputedActivityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'church_id' => $this->churchId,
            'church' => $this->when($this->church !== null, fn () => new ChurchResource($this->church)),
            'type' => Str::replace('-', ' ', $this->type),
            'start_time' => $this->startTime,
            'end_time' => $this->endTime,
            'language' => $this->language,
            'activity_type_description' => $this->activityTypeDescription,
            'date' => $this->date->toDateString(),
            'source' => $this->source,
            'source_id' => $this->sourceId,
            'holiday_name' => $this->holidayName,
            'tags' => $this->tags,
        ];
    }
}
