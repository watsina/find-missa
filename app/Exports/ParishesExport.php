<?php

namespace App\Exports;

use App\Models\Parish;
use App\Models\Diocese;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ParishesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Diocese $diocese) {}

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->diocese->parishes()->orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Parish Name', 'Sub Parish Name', 'Town', 'District', 'Website URL', 'Contact Email', 'Office Address', 'Church Name', 'Church Contact Phone', 'Latitude', 'Longitude', 'Google Maps Link'];
    }

    public function map($parish): array
    {
        $subParishesInfo = $this->subParishInfo($parish);
        $parishInfo = $this->parishInfo($parish);

        if (empty($subParishesInfo)) {
            return $parishInfo;
        }

        return [$parishInfo, ...$subParishesInfo];
    }

    protected function subParishInfo(Parish $parish): array
    {
        $subparishes = $parish->subParishes()->get();

        $data = [];

        if (empty($subparishes)) {
            return $data;
        }

        foreach ($subparishes as $subParish) {
            $church = $subParish->churches()->first();
            $data[] = [
                $parish->name,
                $subParish->name,
                $subParish->city,
                $subParish->district,
                $subParish->website_url,
                $subParish->contact_email,
                $subParish->office_address,
                $church?->name,
                $church?->contact_phone,
                $church?->latitude,
                $church?->longitude,
                $church?->google_maps_link,
            ];
        }

        return $data;
    }

    protected function parishInfo(Parish $parish): array
    {
        $church = $parish->churches()->first();

        return [
            $parish->name,
            '',
            $parish->city,
            $parish->district,
            $parish->website_url,
            $parish->contact_email,
            $parish->office_address,
            $church?->name,
            $church?->contact_phone,
            $church?->latitude,
            $church?->longitude,
            $church?->google_maps_link,
        ];
    }
}
