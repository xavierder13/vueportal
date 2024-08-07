<?php

namespace App\Exports;

use App\EmployeeMasterData;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class EmployeeMasterDataExport implements FromCollection, WithHeadings
{
    protected $params;

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function collection()
    {   
        $params = $this->params;
        return DB::table('employee_master_data as a')
                 ->leftJoin('branches as b', 'a.branch_id', '=', 'b.id')
                 ->leftJoin('companies as c', 'b.company_id', '=', 'c.id')
                 ->leftJoin('departments as d', 'a.department_id', '=', 'd.id')
                 ->leftJoin('divisions as e', 'd.division_id', '=', 'c.id')
                 ->leftJoin('positions as f', 'a.position_id', '=', 'a.id')
                 ->leftJoin('ranks as g', 'f.rank_id', '=', 'g.id')
                 ->where(function($query) use ($params) {
                    if($params['branch_id'] <> 0)
                    {
                        $query->where('branch_id', $params['branch_id']);
                    }
                })
                ->whereDate($params['date_field_param'], '>=', $params['date_from'])
                ->whereDate($params['date_field_param'], '<=', $params['date_to'])
                ->selectRaw("
                    b.name as branch,
                    c.name as company,
                    a.employee_code,
                    a.last_name,
                    a.first_name,
                    a.middle_name,
                    date_format(a.dob,'%m/%d/%Y') as birth_date,
                    a.address,
                    a.contact,
                    a.email,
                    f.name as job_description,	
                    g.name as employee_rank,	
                    d.name as department,
                    e.name as division,		
                    date_format(a.date_employed,'%m/%d/%Y') as date_employed,
                    date_format(a.date_resigned,'%m/%d/%Y') as date_resigned,
                    a.gender,	
                    a.civil_status,	
                    a.tin_no,	
                    a.pagibig_no,	
                    a.philhealth_no,
                    a.sss_no,
                    a.educ_attain,
                    a.school_attended,
                    a.course,
                    CONCAT(FLOOR((TIMESTAMPDIFF(DAY, date_employed, date_format(IFNULL(date_resigned, NOW()),'%Y-%m-%d')) / 365)), ' years(s) ',
                    FLOOR(((TIMESTAMPDIFF(DAY, date_employed, date_format(IFNULL(date_resigned, NOW()),'%Y-%m-%d')) % 365) / 30)), ' month(s) ',
                    ((TIMESTAMPDIFF(DAY, date_employed, date_format(IFNULL(date_resigned, NOW()),'%Y-%m-%d')) % 365) % 30), ' day(s)')  as length_of_service
                ")
                ->get();
    }

    public function headings(): array
    {
        return [
            'BRANCH',
            'COMPANY',	
            'EMPLOYEE CODE/ID',	
            'LAST NAME',	
            'FIRST NAME	',
            'MIDDLE NAME',    
            'DATE OF BIRTH (MM/DD/YR)',	   
            'HOME ADDRESS',	
            'MOBILE NO.',	
            'EMAIL ADDRESS',
            'JOB DESCRIPTION',	
            'EMPLOYEE RANK',	
            'DEPARTMENT',	
            'DIVISION',	
            'DATE EMPLOYED (MM/DD/YR)',	
            'DATE RESIGNED (MM/DD/YR)',
            'GENDER',	
            'CIVIL STATUS',	
            'TIN NO.',	
            'PAG-IBIG NO.',	
            'PHILHEALTH NO.',	
            'SSS NO.',	
            'EDUCATIONAL ATTAINMENT',
            'SCHOOL ATTENDED',
            'COURSE',
            'LENGTH OF SERVICE',
																				
        ];
    }
}
