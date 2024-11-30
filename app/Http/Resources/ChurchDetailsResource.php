<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChurchDetailsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'details' => [
                'id' => $this->id,
                'name' => $this->name,
                'type' => $this->type,
                'coordinates' => $this->when($this->longitude || $this->latitude, "($this->longitude, $this->latitude)"),
                'street_address' => $this->street_address,
                'city' => $this->city,
                'district' => $this->district,
                'postal_address' => $this->postal_address,
                'contact_email' => $this->contact_email,
                'website_url' => $this->website_url,
                'contact_phone' => $this->contact_phone,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'parish' => $this->whenLoaded('parishable'),
            ],
            'mass' => $this->activities()->whereType('mass')->get(),
            'confession' => $this->activities()->whereType('confession')->get(),
            'adoration' => $this->activities()->whereType('adoration')->get(),
        ];
    }
}
