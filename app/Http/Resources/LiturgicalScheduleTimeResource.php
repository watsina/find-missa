<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LiturgicalScheduleTimeResource extends JsonResource
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
            'liturgical_schedule_id' => $this->liturgical_schedule_id,
            'type' => $this->type,
            'activity_type_description' => $this->activity_type_description,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'language' => $this->language,
        ];
    }
}
