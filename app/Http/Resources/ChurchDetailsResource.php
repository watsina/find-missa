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
                'google_maps_link' => $this->google_maps_link,
                'contact_phone' => $this->contact_phone,
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'parish' => $this->whenLoaded('parishable'),
            ],
            'mass' => $this->activities()->whereIn('type', ['mass', 'anticipation-mass', 'traditional-latin-mass'])->get(),
            'confession' => $this->activities()->where('type', 'confession')->get(),
            'adoration' => $this->activities()->where('type', 'adoration')->get(),
        ];
    }
}
