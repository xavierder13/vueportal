<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsTemplate implements FromCollection, WithHeadings
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
        
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'PRODUCT CATEGORY',
            'SERIAL',
            'QUANTITY'
        ];
    }
}
