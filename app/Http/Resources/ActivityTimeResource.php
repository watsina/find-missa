<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
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
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'language' => $this->language,
            'church_id' => $this->church_id,
            'occurrence' => $this->occurrence,
            'date' => $this->date,
            'day_of_week' => $this->day_of_week,
        ];
    }
}
