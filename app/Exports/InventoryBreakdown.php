<?php

namespace App\Exports;
use App\InventoryReconciliationBreakdown;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;

class InventoryBreakdown  extends \PhpOffice\PhpSpreadsheet\Cell\StringValueBinder implements WithCustomValueBinder, FromCollection, WithHeadings 
{
    protected $inventory_recon_id;

    public function __construct($inventory_recon_id)
    {
        $this->inventory_recon_id = $inventory_recon_id;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {   
        $products = InventoryReconciliationBreakdown::select('brand', 'model', 'product_category', 'sap_serial', 'physical_serial')
                                                    ->where('inventory_recon_id', $this->inventory_recon_id)
                                                    ->get();

        return $products;    
    }

    public function headings(): array
    {
        return [
            'BRAND',
            'MODEL',
            'CATEGORY',
            'SAP SERIAL',
            'BRANCH SERIAL',
        ];
    }
}
