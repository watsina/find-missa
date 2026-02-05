<?php

namespace App\Http\Resources;

use App\Models\Parish;
use App\Models\SubParish;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Resource for church data in public-facing APIs.
 * Excludes internal IDs and sensitive relationships.
 */
class PublicChurchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code_name' => $this->code_name,
            'type' => $this->type,
            'coordinates' => $this->when($this->longitude || $this->latitude, "($this->latitude, $this->longitude)"),
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
            'hierarchy' => $this->buildHierarchy(),
        ];
    }

    /**
     * Build the parish hierarchy with names only.
     *
     * @return array<string, string|null>
     */
    protected function buildHierarchy(): array
    {
        $parishable = $this->parishable;

        if ($parishable === null) {
            return [
                'sub_parish' => null,
                'parish' => null,
                'diocese' => null,
            ];
        }

        // Church belongs directly to a Parish
        if ($parishable instanceof Parish) {
            return [
                'sub_parish' => null,
                'parish' => $parishable->name,
                'diocese' => $parishable->diocese?->name,
            ];
        }

        // Church belongs to a SubParish
        if ($parishable instanceof SubParish) {
            return [
                'sub_parish' => $parishable->name,
                'parish' => $parishable->parish?->name,
                'diocese' => $parishable->parish?->diocese?->name,
            ];
        }

        return [
            'sub_parish' => null,
            'parish' => null,
            'diocese' => null,
        ];
    }
}
