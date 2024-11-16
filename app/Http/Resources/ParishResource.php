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
            'contact_email' => $this->contact_email,
            'churches_count' => $this->whenCounted('churches'),
            'sub_parishes_count' => $this->whenCounted('subParishes'),
            'sub_parishes' => SubParishResource::collection($this->whenLoaded('subParishes')),
            'churches' => ChurchResource::collection($this->whenLoaded('churches')),
            'website_url' => $this->website_url,
            'office_address' => $this->office_address,
            'city' => $this->city,
        ];
    }
}
