<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayInstanceResource extends JsonResource
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
            'holiday_id' => $this->holiday_id,
            'year' => $this->year,
            'date' => $this->date?->toDateString(),
            'display_name' => $this->display_name,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'holiday' => new HolidayResource($this->whenLoaded('holiday')),
        ];
    }
}
