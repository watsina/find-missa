<?php

namespace App\Http\Resources;

use App\Enums\ScheduleScope;
use Illuminate\Http\Request;
use App\Enums\ScheduleAction;
use Illuminate\Http\Resources\Json\JsonResource;

class ScheduleOverrideResource extends JsonResource
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
            'scope_type' => $this->scope_type instanceof ScheduleScope
                ? $this->scope_type->value
                : $this->scope_type,
            'scope_id' => $this->scope_id,
            'name' => $this->name,
            'reason' => $this->reason,
            'start_date' => $this->start_date?->toDateString(),
            'end_date' => $this->end_date?->toDateString(),
            'action' => $this->action instanceof ScheduleAction
                ? $this->action->value
                : $this->action,
            'activity_type' => $this->activity_type,
            'target_times' => $this->target_times,
            'target_day_patterns' => $this->target_day_patterns,
            'replacement_times' => $this->replacement_times,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
