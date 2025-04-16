<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActivityTimeDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'type' => Str::replace('-', ' ', $this->type),
            'activity_type_description' => $this->activity_type_description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'language' => $this->language,
            'church_id' => $this->church_id,
            'church' => new ChurchResource($this->whenLoaded('church')),
            'exceptions' => ActivityTimeExceptionResource::collection($this->whenLoaded('exceptions')),
            'occurrence' => $this->occurrence,
            'date' => $this->date,
            'day_of_week' => $this->day_of_week,
        ];
    }
}
