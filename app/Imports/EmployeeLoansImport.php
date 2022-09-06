<?php

namespace App\Imports;

use App\EmployeeLoans;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;

class EmployeeLoansImport implements ToModel
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
            return new EmployeeLoans([
                'branch_id' => $this->branch_id,
                'last_name' => @$row[0],	
                'first_name' => @$row[1],	
                'middle_name' => @$row[2],	
                'position' => @$row[3],	
                'loan_type' => @$row[4],	
                'description' => @$row[5],
                'ref_no' => @$row[6],
                'date_granted' => @$row[7],	
                'principal_loan' => @$row[8],	
                'interest' => @$row[9],	
                'total_loan' => @$row[10],	
                'terms' => @$row[11],	
                'period_from' => @$row[12],	
                'period_to' => @$row[13],	
                'monthly_amortization' => @$row[14],	
                'total_paid' => @$row[15],	
                'balance' => @$row[16],	
                'remaining_term' => @$row[17],	
                'or_number' => @$row[18],	
            ]);
        }

        ++$this->rows;
    }
}
