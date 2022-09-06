<?php

namespace App\Imports;

use App\EmployeePremiums;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class EmployeePremiumsImport implements ToModel
{   

    private $rows = 0;

    use Importable;  

    protected $branch_id;

    public function __construct($branch_id)
    {
        $this->branch_id = $branch_id;
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
            return new EmployeePremiums([
                'branch_id' => $this->branch_id,
                'last_name' => @$row[0],	
                'first_name' => @$row[1],
                'middle_name' => @$row[2],
                'dob' => @$row[3],
                'date_hired' => @$row[4],
                'or_number' => @$row[5],
                'sss_no' => @$row[6],
                'sss_ee' => @$row[7],
                'sss_er' => @$row[8],
                'sss_ec' => @$row[9],
                'sss_total' => @$row[10],
                'philhealth_no' => @$row[11],
                'philhealth_ee' => @$row[12],
                'philhealth_er' => @$row[13],
                'philhealth_total' => @$row[14],
                'pagibig_no' => @$row[15],
                'pagibig_ee' => @$row[16],
                'pagibig_er' => @$row[17],
                'pagibig_total' => @$row[18],
            ]);
        }

        ++$this->rows;
    }
}
