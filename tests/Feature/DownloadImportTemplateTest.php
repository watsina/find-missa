<?php

use App\Models\Team;
use App\Models\User;

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

describe('DownloadImportTemplateController', function () {
    it('requires authentication to download templates', function () {
        $this->getJson('/api/v1/import-templates/masses')
            ->assertUnauthorized();
    });

    it('requires data.import permission for non-admin user', function () {
        $this->actingAs($this->regularUser)
            ->getJson('/api/v1/import-templates/masses')
            ->assertForbidden();
    });

    it('downloads the masses template with correct headers and multiple samples', function () {
        $response = $this->actingAs($this->adminUser)
            ->get('/api/v1/import-templates/masses')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=masses_template.csv');

        $content = $response->streamedContent();
        expect($content)->toContain('diocese_name,parish_name,parish_town,parish_district,sub_parish_name,sub_parish_town,sub_parish_district,church_name,city,day_of_week,language,start_time,end_time,activity_type')
            ->and($content)->toContain('"Kampala Archdiocese","Ntinda Parish",Ntinda,Kampala,,,,"St Charles Church",Ntinda,Sunday,English,07:00,08:30,Mass')
            ->and($content)->toContain('"Kampala Archdiocese","Ntinda Parish",Ntinda,Kampala,,,,"St Charles Church",Ntinda,Weekdays,English,07:00,07:30,Mass')
            ->and($content)->toContain('"Kampala Archdiocese","Ntinda Parish",Ntinda,Kampala,"Bukoto SubParish",Bukoto,Kampala,"St Jude Chapel",Bukoto,"Monday, Wednesday, Friday",Luganda,17:00,18:00,Mass');
    });

    it('downloads the parishes template with correct headers and multiple samples including subparishes and chapels', function () {
        $response = $this->actingAs($this->adminUser)
            ->get('/api/v1/import-templates/parishes')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=parishes_template.csv');

        $content = $response->streamedContent();
        expect($content)->toContain('diocese_name,diocese_type,parish_name,parish_town,church_name,church_type,latitude,longitude,city')
            ->and($content)->toContain('"Kampala Archdiocese",archdiocese,"Ntinda Parish",Ntinda,"St Charles Lwanga Church",parish,0.3541,32.6105,Ntinda')
            ->and($content)->toContain('"Kampala Archdiocese",archdiocese,"Ntinda Parish",Ntinda,"Bukoto Sub-Parish Church",sub-parish,0.3600,32.6000,Bukoto')
            ->and($content)->toContain('"Kampala Archdiocese",archdiocese,"Ntinda Parish",Ntinda,"St Kizito Chapel",chapel,0.3500,32.6200,Naguru');
    });

    it('downloads the churches template with correct headers and samples', function () {
        $response = $this->actingAs($this->adminUser)
            ->get('/api/v1/import-templates/churches')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=diocese_churches_template.csv');

        $content = $response->streamedContent();
        expect($content)->toContain('parish_name,sub_parish_name,church_name,town,district,office_address,contact_email,website_url,google_maps_link,church_contact_phone,postal_address,latitude,longitude')
            ->and($content)->toContain('"Ntinda Parish",,"St Charles Church",Ntinda,Kampala,"Plot 123 Main Rd",office@example.com,https://example.com,https://maps.google.com,+256700000000,"P.O. Box 1234",0.3541,32.6105')
            ->and($content)->toContain('"Ntinda Parish","Bukoto SubParish","St Jude Chapel",Bukoto,Kampala,"Plot 45 Bukoto St",bukoto@example.com,https://example.com/bukoto');
    });

    it('downloads the activities template with correct headers and multi-day samples', function () {
        $response = $this->actingAs($this->adminUser)
            ->get('/api/v1/import-templates/activities')
            ->assertOk()
            ->assertHeader('Content-Disposition', 'attachment; filename=diocese_activities_template.csv');

        $content = $response->streamedContent();
        expect($content)->toContain('parish_name,sub_parish_name,church_name,day_of_week,language,start_time,end_time,activity_type')
            ->and($content)->toContain('"Ntinda Parish",,"St Charles Church",Sunday,English,07:00,08:30,Mass')
            ->and($content)->toContain('"Ntinda Parish",,"St Charles Church",Weekdays,English,07:00,07:30,Mass');
    });

    it('returns 404 for an unknown template type', function () {
        $this->actingAs($this->adminUser)
            ->get('/api/v1/import-templates/unknown_type')
            ->assertNotFound();
    });
});
