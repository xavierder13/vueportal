<?php

namespace App\Exports;

use App\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;
use Auth;

class ProductsExport implements FromCollection, WithHeadings
{
    /**
    * @return \Illuminate\Support\Collection
    */
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }
    
    public function collection()
    {   
        $params = $this->params;
   
        $products = DB::table('products')
                      ->join('brands', 'products.brand_id', '=', 'brands.id')
                      ->leftJoin('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                      ->select(DB::raw('brands.name as brand'), 'products.model', DB::raw('product_categories.name as product_category'), 'products.serial')
                      ->where(function($query) use ($params){
                            
                        if(isset($params['branch_id']))
                        {
                            $query->where('products.branch_id', '=', $params['branch_id']);
                        }
                        
                        if(isset($params['file_upload_log_id']))
                        {
                            $query->where('products.file_upload_log_id', '=', $params['file_upload_log_id']);
                        }
                         
                      })
                      ->orderBy('brands.name', 'Asc')
                      ->orderBy('products.model', 'Asc')
                      ->orderBy('product_categories.name', 'Asc')
                      ->orderBy('products.serial', 'Asc')
                      ->get();

        return $products;
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
