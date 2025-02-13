<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DioceseResource extends JsonResource
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
            'contact_email' => $this->contact_email,
            'parishes_count' => $this->whenCounted('parishes'),
            'parishes' => ParishResource::collection($this->whenLoaded('parishes')),
            'website_url' => $this->website_url,
            'office_address' => $this->office_address,
            'postal_address' => $this->postal_address,
        ];
    }
}
