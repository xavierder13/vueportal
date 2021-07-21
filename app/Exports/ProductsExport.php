<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ProductsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */

    
    public function collection()
    {   
        $products = DB::table('products')
                      ->join('brands', 'products.brand_id', '=', 'brands.id')
                      ->select('brands.name', 'products.model', 'products.serial')
                      ->get();
        return $products;
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'SERIAL',
            'QUANTITY'
        ];
    }

}
