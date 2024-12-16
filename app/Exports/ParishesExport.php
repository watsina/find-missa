<?php

namespace App\Exports;

use App\Models\Diocese;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ParishesExport implements FromCollection, ShouldAutoSize, WithHeadings, WithMapping
{
    public function __construct(protected Diocese $diocese) {}

    public function headings(): array
    {
        return ['Name', 'Town', 'District', 'Website URL', 'Contact Email', 'Office Address', 'Church Name', 'Church Contact Phone', 'Latitude', 'Longitude', 'Google Maps Link'];
    }

    public function map($parish): array
    {
        $church = $parish->churches()->first();

        return [
            $parish->name,
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

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->diocese->parishes()->get();
    }
}
