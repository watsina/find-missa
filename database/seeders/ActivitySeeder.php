<?php

namespace Database\Seeders;

use App\Models\Team;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Models\ActivityTime;
use Illuminate\Database\Seeder;

class ActivitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = require_once __DIR__.'/activity_data.php';

        $team = Team::where('country', 'Uganda')->firstOrFail();

        foreach ($data as $info) {
            $diocese = Diocese::create([...$info['diocese_details'], 'team_id' => $team->id]);
            foreach ($info['parishes'] as $parishInfo) {
                $parish = Parish::create([...$parishInfo['details'], 'diocese_id' => $diocese->id, 'team_id' => $team->id]);
                foreach ($parishInfo['church_details'] as $churchInfo) {
                    $church = Church::create([...$churchInfo['details'], 'parishable_type' => 'parish', 'parishable_id' => $parish->id, 'team_id' => $team->id]);
                    if (isset($churchInfo['activity_details'])) {
                        foreach ($churchInfo['activity_details'] as $activity) {
                            ActivityTime::create([...$activity, 'church_id' => $church->id, 'team_id' => $team->id]);
                        }
                    }
                }
            }
        }
    }
}
