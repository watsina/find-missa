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
            'parish' => new ParishResource($this->whenLoaded('parish')),
            'churches_count' => $this->whenCounted('churches'),
            'contact_email' => $this->contact_email,
            'website_url' => $this->website_url,
            'parish_id' => $this->parish_id,
            'city' => $this->city,
            'office_address' => $this->office_address,
        ];
    }
}
