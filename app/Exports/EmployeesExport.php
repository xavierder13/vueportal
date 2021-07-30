<?php

namespace App\Exports;

use App\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class EmployeesExport implements FromCollection, WithHeadings
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

        $employees = DB::table('employees')
                      ->join('branches', 'employees.branch_id', '=', 'branches.id')
                      ->select('branches.name', 
                                'employees.employee_code',
                                'employees.last_name',
                                'employees.first_name',
                                'employees.middle_name',
                                DB::raw("DATE_FORMAT(employees.dob, '%m.%d.%Y') as dob"),
                                'employees.address',
                                'employees.contact',
                                'employees.email',
                                'employees.class',
                                'employees.rank',
                                'employees.department',
                                'employees.cost_center_code',
                                'employees.job_description',
                                DB::raw("DATE_FORMAT(employees.date_employed, '%m.%d.%Y') as date_employed"),
                                'employees.gender',
                                'employees.civil_status',
                                'employees.tax_status',
                                'employees.tin_no',
                                'employees.tax_branch_code',
                                'employees.pagibig_no',
                                'employees.philhealth_no',
                                'employees.sss_no',
                                'employees.time_schedule',
                                'employees.restday')
                      ->where(function($query) use ($branch_id){
                          if($branch_id <> 0)
                          {
                              $query->where('employees.branch_id', '=', $branch_id);
                          }
                      })
                      ->get();
        return $employees;
    }

    public function headings(): array
    {
        return [
            'BRANCH',	
            'EMPLOYEE CODE/ID',	
            'LAST NAME',	
            'FIRST NAME	',
            'MIDDLE NAME',    
            'DATE OF BIRTH (MM/DD/YR)',	   
            'CURRENT  ADDRESS',	
            'MOBILE NO.',	
            'EMAIL ADDRESS',	
            'EMPLOYEE CLASS',	
            'EMPLOYEE RANK',	
            'DEPARTMENT CODE',	
            'COST CENTER CODE',	
            'JOB DESCRIPTION',	
            'DATE EMPLOYED (MM/DD/YR)',	
            'GENDER',	
            'STATUS',	
            'TAX STATUS',	
            'TIN NO.',	
            'TAX BRANCH CODE',	
            'PAG-IBIG NO.',	
            'PHILHEALTH NO.',	
            'SSS NO.',	
            'TIME SCHEDULE',	
            'RESTDAY',
																							
        ];
    }
}
