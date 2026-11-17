<?php

use App\Models\Team;
use App\Models\User;
use App\Models\Church;
use App\Models\Parish;
use App\Models\Diocese;
use App\Enums\DayPattern;
use App\Models\SubParish;
use App\Models\ActivityTime;
use App\Actions\ClearTeamData;
use App\Models\AdorationChapel;
use App\Models\ActivitySchedule;
use App\Imports\ActivitiesImport;
use App\Imports\MassesDataImport;
use Illuminate\Http\UploadedFile;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\ParishChurchDataImport;
use App\Http\Resources\ChurchDetailsResource;

beforeEach(function () {
    $this->team = Team::factory()->create();
    $this->adminUser = User::factory()->create([
        'is_super_admin' => true,
        'current_team_id' => $this->team->id,
    ]);
    $this->regularUser = User::factory()->create([
        'is_super_admin' => false,
        'current_team_id' => $this->team->id,
    ]);
    session()->put('current_team_id', $this->team->id);
});

describe('MassesDataImport', function () {
    it('imports diocese, parishes, churches, and creates v2 activity schedules alongside legacy activity times', function () {
        $csvContent = implode("\n", [
            'diocese_name,parish_name,parish_town,parish_district,sub_parish_name,sub_parish_town,sub_parish_district,church_name,city,day_of_week,language,start_time,end_time,activity_type',
            'Kampala Archdiocese,Ntinda Parish,Ntinda,Kampala,,, ,St Charles Church,Ntinda,Sunday,English,07:00,08:30,Mass',
            'Kampala Archdiocese,Ntinda Parish,Ntinda,Kampala,,, ,St Charles Church,Ntinda,Saturday,English,17:30,19:00,Anticipation Mass',
            'Kampala Archdiocese,Ntinda Parish,Ntinda,Kampala,,, ,St Charles Church,Ntinda,Weekdays,English,7:00,07:30,Mass',
            'Kampala Archdiocese,Ntinda Parish,Ntinda,Kampala,Bukoto SubParish,Bukoto,Kampala,St Jude Church,Bukoto,"Monday, Wednesday, Friday",Luganda,17:00,,Mass',
            'Kampala Archdiocese,Ntinda Parish,Ntinda,Kampala,,, ,St Charles Church,Ntinda,Daily,English,06:00,,Adoration',
        ]);

        $file = UploadedFile::fake()->createWithContent('masses.csv', $csvContent);

        Excel::import(new MassesDataImport($this->team), $file);

        // Verify Diocese
        $diocese = Diocese::where('name', 'Kampala Archdiocese')->first();
        expect($diocese)->not->toBeNull();
        expect($diocese->team_id)->toBe($this->team->id);

        // Verify Parish
        $parish = Parish::where('name', 'Ntinda Parish')->where('diocese_id', $diocese->id)->first();
        expect($parish)->not->toBeNull();
        expect($parish->city)->toBe('Ntinda');

        // Verify SubParish created for St Jude Church
        $subParish = SubParish::where('name', 'Bukoto SubParish')->where('parish_id', $parish->id)->first();
        expect($subParish)->not->toBeNull();

        // Verify no blank-named sub-parish was created for St Charles
        expect(SubParish::where('name', '')->orWhereNull('name')->count())->toBe(0);

        // Verify Churches
        $mainChurch = Church::where('name', 'St Charles Church')->first();
        expect($mainChurch)->not->toBeNull();
        expect($mainChurch->parishable_type)->toBe('parish');
        expect($mainChurch->parishable_id)->toBe($parish->id);

        $subChurch = Church::where('name', 'St Jude Church')->first();
        expect($subChurch)->not->toBeNull();
        expect($subChurch->parishable_type)->toBe('subParish');
        expect($subChurch->parishable_id)->toBe($subParish->id);

        // Verify V2 Activity Schedules
        $sundaySchedule = ActivitySchedule::where('church_id', $mainChurch->id)
            ->where('start_time', '07:00')
            ->first();
        expect($sundaySchedule)->not->toBeNull();
        expect($sundaySchedule->day_pattern)->toBe(DayPattern::Sunday);
        expect($sundaySchedule->type)->toBe('mass');
        expect($sundaySchedule->end_time)->toBe('08:30');
        expect($sundaySchedule->team_id)->toBe($this->team->id);

        $saturdaySchedule = ActivitySchedule::where('church_id', $mainChurch->id)
            ->where('start_time', '17:30')
            ->first();
        expect($saturdaySchedule)->not->toBeNull();
        expect($saturdaySchedule->day_pattern)->toBe(DayPattern::Saturday);
        expect($saturdaySchedule->type)->toBe('anticipation-mass');

        $weekdaysSchedule = ActivitySchedule::where('church_id', $mainChurch->id)
            ->where('start_time', '07:00')
            ->where('day_pattern', DayPattern::Weekdays)
            ->first();
        expect($weekdaysSchedule)->not->toBeNull();

        $specificSchedule = ActivitySchedule::where('church_id', $subChurch->id)
            ->where('start_time', '17:00')
            ->first();
        expect($specificSchedule)->not->toBeNull();
        expect($specificSchedule->day_pattern)->toBe(DayPattern::Specific);
        expect($specificSchedule->days_of_week)->toBe(['Monday', 'Wednesday', 'Friday']);

        $dailySchedule = ActivitySchedule::where('church_id', $mainChurch->id)
            ->where('day_pattern', DayPattern::Daily)
            ->first();
        expect($dailySchedule)->not->toBeNull();
        expect($dailySchedule->type)->toBe('adoration');

        // Verify Legacy ActivityTime records were also created
        expect(ActivityTime::where('church_id', $mainChurch->id)->count())->toBeGreaterThan(0);
        expect(ActivityTime::where('church_id', $mainChurch->id)->where('day_of_week', 'Sunday')->exists())->toBeTrue();
        expect(ActivityTime::where('church_id', $subChurch->id)->where('day_of_week', 'Monday')->exists())->toBeTrue();
        expect(ActivityTime::where('church_id', $subChurch->id)->where('day_of_week', 'Wednesday')->exists())->toBeTrue();
        expect(ActivityTime::where('church_id', $subChurch->id)->where('day_of_week', 'Friday')->exists())->toBeTrue();

        // Verify Church Details Resource formats regular schedule
        $mainChurch->load('activitySchedules');
        $resource = (new ChurchDetailsResource($mainChurch))->toArray(request());
        expect($resource['regular_schedule']['mass']['sunday'])->not->toBeEmpty();
        expect($resource['regular_schedule']['mass']['saturday'])->not->toBeEmpty();
        expect($resource['regular_schedule']['mass']['weekdays'])->not->toBeEmpty();
        expect($resource['regular_schedule']['adoration']['daily'])->not->toBeEmpty();
    });

    it('is idempotent when re-importing the same rows', function () {
        $csvContent = implode("\n", [
            'diocese_name,parish_name,parish_town,parish_district,sub_parish_name,sub_parish_town,sub_parish_district,church_name,city,day_of_week,language,start_time,activity_type',
            'Jinja Diocese,Rubaga Parish,Jinja,Jinja,,, ,Rubaga Cathedral,Jinja,Sunday,English,08:00,Mass',
        ]);

        $file1 = UploadedFile::fake()->createWithContent('masses.csv', $csvContent);
        Excel::import(new MassesDataImport($this->team), $file1);

        $initialSchedulesCount = ActivitySchedule::count();
        $initialActivityTimesCount = ActivityTime::count();

        $file2 = UploadedFile::fake()->createWithContent('masses_repeat.csv', $csvContent);
        Excel::import(new MassesDataImport($this->team), $file2);

        expect(ActivitySchedule::count())->toBe($initialSchedulesCount);
        expect(ActivityTime::count())->toBe($initialActivityTimesCount);
    });
});

describe('ActivitiesImport', function () {
    it('imports activities for an existing diocese into v2 schedules and legacy times', function () {
        $diocese = Diocese::factory()->create(['name' => 'Gulu Archdiocese', 'team_id' => $this->team->id]);
        $parish = Parish::factory()->create(['name' => 'Holy Rosary Parish', 'diocese_id' => $diocese->id, 'team_id' => $this->team->id]);
        $church = Church::factory()->create([
            'name' => 'Holy Rosary Church',
            'parishable_id' => $parish->id,
            'parishable_type' => 'parish',
            'team_id' => $this->team->id,
        ]);

        $csvContent = implode("\n", [
            'parish_name,sub_parish_name,church_name,day_of_week,language,start_time,activity_type',
            'Holy Rosary Parish,,Holy Rosary Church,Sunday,Acholi,06:30,Mass',
            'Holy Rosary Parish,,Holy Rosary Church,Saturday,English,08:00,Confession',
        ]);

        $file = UploadedFile::fake()->createWithContent('activities.csv', $csvContent);
        Excel::import(new ActivitiesImport($diocese), $file);

        expect(ActivitySchedule::where('church_id', $church->id)->count())->toBe(2);
        expect(ActivitySchedule::where('church_id', $church->id)->where('type', 'mass')->where('day_pattern', DayPattern::Sunday)->exists())->toBeTrue();
        expect(ActivitySchedule::where('church_id', $church->id)->where('type', 'confession')->where('day_pattern', DayPattern::Saturday)->exists())->toBeTrue();

        expect(ActivityTime::where('church_id', $church->id)->count())->toBe(2);
    });
});

describe('ParishChurchDataImport', function () {
    it('imports dioceses, parishes, and churches', function () {
        $csvContent = implode("\n", [
            'diocese_name,diocese_type,parish_name,parish_town,church_name,church_type,latitude,longitude,city',
            'Arua Diocese,diocese,Ediofe Parish,Arua,Ediofe Cathedral,parish,3.0300,30.9100,Arua',
        ]);

        $file = UploadedFile::fake()->createWithContent('parishes.csv', $csvContent);
        Excel::import(new ParishChurchDataImport($this->team), $file);

        $diocese = Diocese::where('name', 'Arua Diocese')->first();
        expect($diocese)->not->toBeNull();

        $parish = Parish::where('name', 'Ediofe Parish')->first();
        expect($parish)->not->toBeNull();

        $church = Church::where('name', 'Ediofe Cathedral')->first();
        expect($church)->not->toBeNull();
        expect($church->latitude)->toBe('3.0300');
    });
});

describe('ImportDataController', function () {
    it('requires authentication', function () {
        $file = UploadedFile::fake()->create('test.csv', 10);

        $this->postJson('/api/v1/import-data', [
            'type' => 'masses',
            'file' => $file,
        ])->assertUnauthorized();
    });

    it('requires data.import permission for regular user', function () {
        $file = UploadedFile::fake()->create('test.csv', 10);

        $this->actingAs($this->regularUser)
            ->postJson('/api/v1/import-data', [
                'type' => 'masses',
                'file' => $file,
            ])->assertForbidden();
    });

    it('successfully imports masses via controller for authorized admin', function () {
        $csvContent = implode("\n", [
            'diocese_name,parish_name,parish_town,parish_district,sub_parish_name,sub_parish_town,sub_parish_district,church_name,city,day_of_week,language,start_time,activity_type',
            'Mbarara Archdiocese,Nyamitanga Parish,Mbarara,Mbarara,,, ,Our Lady of Perpetual Help,Mbarara,Sunday,Runyankore,07:00,Mass',
        ]);

        $file = UploadedFile::fake()->createWithContent('masses.csv', $csvContent);

        $this->actingAs($this->adminUser)
            ->postJson('/api/v1/import-data', [
                'type' => 'masses',
                'file' => $file,
            ])->assertNoContent();

        expect(Diocese::where('name', 'Mbarara Archdiocese')->exists())->toBeTrue();
        expect(ActivitySchedule::where('start_time', '07:00')->where('day_pattern', DayPattern::Sunday)->exists())->toBeTrue();
    });

    it('successfully imports churches for a diocese via import-churches endpoint', function () {
        $diocese = Diocese::factory()->create(['team_id' => $this->team->id]);

        $csvContent = implode("\n", [
            'parish_name,sub_parish_name,church_name,town,district,office_address,contact_email,website_url,google_maps_link,church_contact_phone,postal_address,latitude,longitude',
            'Cathedral Parish,,Sacred Heart Cathedral,Gulu,Gulu,,,,,,,3.00,32.00',
        ]);

        $file = UploadedFile::fake()->createWithContent('churches.csv', $csvContent);

        $this->actingAs($this->adminUser)
            ->postJson("/api/v1/import-churches/{$diocese->id}", [
                'file' => $file,
            ])->assertNoContent();

        expect(Church::where('name', 'Sacred Heart Cathedral')->exists())->toBeTrue();
    });

    it('successfully imports activities for a diocese via import-activities endpoint', function () {
        $diocese = Diocese::factory()->create(['team_id' => $this->team->id]);
        $parish = Parish::factory()->create(['name' => 'St Mary Parish', 'diocese_id' => $diocese->id, 'team_id' => $this->team->id]);
        $church = Church::factory()->create([
            'name' => 'St Mary Church',
            'parishable_id' => $parish->id,
            'parishable_type' => 'parish',
            'team_id' => $this->team->id,
        ]);

        $csvContent = implode("\n", [
            'parish_name,sub_parish_name,church_name,day_of_week,language,start_time,activity_type',
            'St Mary Parish,,St Mary Church,Sunday,English,09:00,Mass',
        ]);

        $file = UploadedFile::fake()->createWithContent('activities.csv', $csvContent);

        $this->actingAs($this->adminUser)
            ->postJson("/api/v1/import-activities/{$diocese->id}", [
                'file' => $file,
            ])->assertNoContent();

        expect(ActivitySchedule::where('church_id', $church->id)->where('start_time', '09:00')->exists())->toBeTrue();
    });
});

describe('ClearTeamData', function () {
    it('truncates adoration chapels along with other domain models', function () {
        $church = Church::factory()->create();
        $chapel = AdorationChapel::factory()->create(['church_id' => $church->id]);

        expect(AdorationChapel::count())->toBe(1);

        app(ClearTeamData::class)->execute();

        expect(AdorationChapel::count())->toBe(0);
        expect(Church::count())->toBe(0);
    });
});
