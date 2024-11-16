<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\User;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Priest;
use App\Models\Diocese;
use App\Models\SubParish;
use App\Models\ActivityTime;
use Illuminate\Database\Seeder;

class LocalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'is_super_admin' => true,
        ]);
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $team = $admin->ownedTeams()->create([
            'name' => 'Team Uganda',
            'country' => 'Uganda',
        ]);

        $team->members()->attach([$user->id]);

        $this->seedData($team);
    }

    protected function seedData(Team $team)
    {
        $dioceses = Diocese::factory(8)->create(['team_id' => $team->id]);
        $parishes = Parish::factory(10)->recycle($dioceses)->create(['team_id' => $team->id]);
        SubParish::factory(15)->recycle($parishes)->create(['team_id' => $team->id]);
        $churches = Church::factory(10)->recycle($parishes)->create(['team_id' => $team->id]);
        ActivityTime::factory(10)->recycle($churches)->create(['team_id' => $team->id]);
        Priest::factory(10)->recycle($parishes)->create(['team_id' => $team->id]);
    }
}
