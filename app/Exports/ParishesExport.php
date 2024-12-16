<?php

namespace App\Exports;

use App\Models\Diocese;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class ParishesExport implements FromCollection, WithHeadings
{
    public function __construct(protected Diocese $diocese) {}

    public function headings(): array
    {
        return ['Name', 'Town', 'District', 'Website URL', 'Contact Email', 'Office Address'];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return $this->diocese->parishes()->get(['name', 'city', 'district', 'website_url', 'contact_email', 'office_address']);
    }
}
