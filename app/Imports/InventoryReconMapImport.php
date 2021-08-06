<?php

namespace App\Imports;

use App\InventoryReconciliationMap;
use Maatwebsite\Excel\Concerns\ToModel;

class InventoryReconMapImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new InventoryReconciliationMap([
            //
        ]);
    }
}
