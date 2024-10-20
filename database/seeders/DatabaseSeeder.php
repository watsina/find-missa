<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_super_admin' => true,
        ]);
        \App\Models\User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // $dioceses = Diocese::factory(10)->create();
        // $parishes = Parish::factory(20)->recycle($dioceses)->create();
        // SubParish::factory(30)->recycle($parishes)->create();
        // $churches = Church::factory(100)->recycle($parishes)->create();
        // ActivityTime::factory()->recycle($churches)->create();
        // Priest::factory(80)->recycle($parishes)->create();
    }
}
