<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'country' => $this->country,
            'users' => $this->users,
            'invitations' => $this->invitations->loadMissing('role:id,name,description'),
            'members' => $this->members->each(fn ($member) => $member['roles'] = $member->roles()->get()),
        ];
    }
}
