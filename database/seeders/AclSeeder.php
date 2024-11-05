<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('acl.permissions') as $permissions) {
            collect($permissions)->each(fn ($permission) => Permission::create($permission));
        }
        foreach (config('acl.roles') as $roles) {
            collect($roles)->each(fn ($role) => Role::create($role));
        }
    }
}
