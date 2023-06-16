<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class InventoryBreakdown  extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements WithCustomValueBinder, FromCollection, WithHeadings 
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        return collect($this->data);    
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'PRODUCT CATEGORY',
            'SAP SERIAL',
            'BRANCH SERIAL',
        ];
    }
}
