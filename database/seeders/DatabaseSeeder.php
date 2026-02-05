<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\App;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AclSeeder::class);
        $this->call(HolidaySeeder::class);

        if (App::environment('local')) {
            $this->call(LocalSeeder::class);
            $this->call(ActivitySeeder::class);
        } elseif (App::environment('staging')) {
            $this->call(StagingSeeder::class);
        }
    }
}
