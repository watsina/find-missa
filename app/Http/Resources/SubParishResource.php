<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SubParishResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'parish' => $this->parish?->name,
            'parish_id' => $this->parish_id,
            'city' => $this->city,
            'office_address' => $this->office_address,
        ];
    }
}
