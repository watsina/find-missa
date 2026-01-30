<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OneTimeActivityResource extends JsonResource
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
            'date' => $this->date?->toDateString(),
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'language' => $this->language,
            'reason' => $this->reason,
            'tags' => ActivityTagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
