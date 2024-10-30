<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'two_factor_enabled' => $this->two_factor_enabled,
            'is_admin' => $this->is_super_admin,
            'email_verified_at' => $this->email_verified_at,
            'profile_photo_url' => $this->profile_photo_url,
            'profile_photo_path' => $this->profile_photo_path,
        ];
    }
}
