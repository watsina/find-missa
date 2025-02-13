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
            'code_name' => $this->code_name,
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
            'parishable_id' => $this->parishable_id,
            'parishable_type' => $this->parishable_type,
            'parish' => $this->whenLoaded('parishable'),
            'priest_id' => $this->priest_id,
            'activities' => ActivityTimeResource::collection($this->whenLoaded('activities')),
        ];
    }
}
