<?php

namespace App\Imports;

use App\InventoryReconciliationMap;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Auth;

class InventoryReconMapImport implements ToModel
{   

    private $rows = 0;

    use Importable;  

    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {   
        // insert all rows except first row(header)
        if($this->rows > 0)
        {
            return new InventoryReconciliationMap([
                'inventory_recon_id' => $this->params['inventory_recon_id'],
                'user_id' => $this->params['user_id'],
                'inventory_type' => $this->params['inventory_type'],
                'brand' => @$row[0],
                'model' => @$row[1],
                'product_category' => @$row[2],
                'serial' => is_numeric(@$row[3]) ? (integer) @$row[3] : @$row[3], // if serial is numeric then convert it to integer due to long serial details
                'quantity' => 1,
            ]);
  
        }

        ++$this->rows;
        
    }
}
