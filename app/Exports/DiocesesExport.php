<?php

namespace App\Exports;

use App\Models\Diocese;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class DiocesesExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return ['Name', 'Type', 'Website Url', 'Contact Email', 'Office Address', 'Postal Address'];
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Diocese::all('name', 'type', 'website_url', 'contact_email', 'office_address', 'postal_address');
    }
}
