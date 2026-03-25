<?php

namespace App\Http\Resources;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * Works with RolePermission models (from role->permissions relationship).
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // RolePermission model has 'permission' field (the slug string)
        $name = $this->permission ?? $this->name;

        return [
            'name' => $name,
            'display_name' => ucwords(Str::replace('.', ' ', $name)),
        ];
    }
}
