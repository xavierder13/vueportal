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
    protected $branch_id;

    public function __construct($branch_id)
    {
        $this->branch_id = $branch_id;
    }
    
    public function collection()
    {   
        $branch_id = $this->branch_id;

        $products = DB::table('products')
                      ->join('brands', 'products.brand_id', '=', 'brands.id')
                      ->select('brands.name', 'products.model', 'products.serial')
                      ->where(function($query) use ($branch_id){
                          if($branch_id <> 0)
                          {
                              $query->where('products.branch_id', '=', $branch_id);
                          }
                      })
                      ->orderBy('brands.name', 'Asc')
                      ->orderBy('products.model', 'Asc')
                      ->orderBy('products.serial', 'Asc')
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
