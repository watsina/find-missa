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
            'coordinates' => "[$this->longitude, $this->latitude]",
            'street_address' => $this->street_address,
            'postal_address' => $this->postal_address,
            'contact_email' => $this->contact_email,
            'contact_phone' => $this->contact_phone,
            // 'parish' => $this->parishable,
            'priest_id' => $this->priest_id,
        ];
    }
}
