<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParishResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'diocese_id' => $this->diocese_id,
            'diocese' => new DioceseResource($this->whenLoaded('diocese')),
            'office_address' => $this->office_address,
            'city' => $this->city,
        ];
    }
}
