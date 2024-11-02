<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
            'name' => $this->name,
            'country' => $this->country,
            'users' => $this->users,
            'teamInvitations' => $this->teamInvitations,
            'availableRoles' => [], // array_values(Jetstream::$roles),
            'availablePermissions' => [], //Jetstream::$permissions,
            'defaultPermissions' => [], // Jetstream::$defaultPermissions,
            'permissions' => [
                'canAddTeamMembers' => Gate::check('addTeamMember', $this),
                'canDeleteTeam' => Gate::check('delete', $this),
                'canRemoveTeamMembers' => Gate::check('removeTeamMember', $this),
                'canUpdateTeam' => Gate::check('update', $this),
                'canUpdateTeamMembers' => Gate::check('updateTeamMember', $this),
            ],
        ];
    }
}
