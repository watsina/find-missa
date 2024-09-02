<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChurchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'street_address' => $this->street_address,
            'postal_address' => $this->postal_address,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            'parish_type' => $this->parish_type,
            'parish_id' => $this->parish_id,
            'priest_id' => $this->priest_id,
        ];
    }
}
