<?php

namespace App\Exports;

use App\Models\Diocese;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class DiocesesExport implements FromCollection, ShouldAutoSize, WithHeadings
{
    public function headings(): array
    {
        return ['Name', 'Type', 'Website Url', 'Contact Email', 'Office Address', 'Postal Address'];
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return Diocese::all('name', 'type', 'website_url', 'contact_email', 'office_address', 'postal_address');
    }
}
