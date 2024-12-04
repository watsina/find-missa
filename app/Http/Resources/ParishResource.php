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
            'contact_email' => $this->contact_email,
            'website_url' => $this->website_url,
            'office_address' => $this->office_address,
            'city' => $this->city,
            'district' => $this->district,
            'diocese' => new DioceseResource($this->whenLoaded('diocese')),
            'churches_count' => $this->whenCounted('churches'),
            'sub_parishes_count' => $this->whenCounted('subParishes'),
            'sub_parishes' => SubParishResource::collection($this->whenLoaded('subParishes')),
            'churches' => ChurchResource::collection($this->whenLoaded('churches')),
            'priests' => PriestResource::collection($this->whenLoaded('priests')),
        ];
    }
}
