<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Silber\Bouncer\BouncerFacade as Bouncer;

class AclSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (config('acl.permissions') as $permissions) {
            collect($permissions)->each(fn ($permission) => Bouncer::ability()->create($permission));
        }
        foreach (config('acl.roles') as $roles) {
            collect($roles)->each(fn ($role) => Bouncer::role()->create($role));
        }
    }
}
