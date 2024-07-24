<?php

namespace App\Exports;

use App\employee_master_data;
use Maatwebsite\Excel\Concerns\FromCollection;

class EmployeeMasterDataExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return employee_master_data::all();
    }
}
