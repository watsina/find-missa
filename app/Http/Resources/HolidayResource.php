<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HolidayResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'type' => $this->type->value,
            'type_label' => $this->type->label(),

            // Fixed date fields
            'fixed_month' => $this->fixed_month,
            'fixed_day' => $this->fixed_day,

            // Easter-relative fields
            'easter_offset_days' => $this->easter_offset_days,

            // Computed fields
            'computation_rule' => $this->computation_rule,

            // Categorization
            'is_holy_day_of_obligation' => $this->is_holy_day_of_obligation,
            'is_universal' => $this->is_universal,
            'is_moveable' => $this->is_moveable,
            'suppresses_regular_schedule' => $this->suppresses_regular_schedule,

            // Liturgical info
            'liturgical_color' => $this->liturgical_color,

            // Team
            'team_id' => $this->team_id,

            // Timestamps
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),

            // Relationships
            'instances' => HolidayInstanceResource::collection($this->whenLoaded('instances')),
        ];
    }
}
