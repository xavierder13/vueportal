<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Positions;
use App\EmployeeMasterData;
use App\RequiredEmployeeMap;
use App\Branch;
use App\Brand;
use DB;

class BranchManpowerReport implements FromCollection, WithHeadings
{
    protected $params;
    protected $positions = [
        'Branch Manager', 
        'Reserved Branch Manager',
        'Sales Supervisor', 
        'Reserved Sales Supervisor', 
        'Credit & Collection Supervisor', 
        'Reserved CCS',
        'Management System Supervisor',
        'Reserved MSS',
        'Sales Specialist',
        'Warehouseman',
        'Driver',
        'Helper',
        'Technician',
        'Account Analyst',
        'Cashier',
        'Bookkeeper - Finance',
        'Bookkeeper - Operations',
    ];

    public function __construct($params)
    {
        $this->params = $params;
    }

    public function collection()
    { 

        $data = [];

        $asOf = $this->params['asOf'];
        $firstOfMonth = $this->params['firstOfMonth'];
        $asOfLastDayLastMonth = $this->params['asOfLastDayLastMonth'];
        $branch_id = $this->params['branch_id'];

        $branches = Branch::where(function($query)  use ($branch_id){
                                if($branch_id <> 0)
                                {
                                    $query->where('id', $branch_id);
                                }
                          })
                          ->get();

        foreach ($branches as $i => $branch) {
            $data[$i] = ['branch' => $branch->name];
            foreach ($this->positions as $j => $position) {
                $required_employee = RequiredEmployeeMap::with('position')
                                                ->whereHas('position', function($query) use ($position) {
                                                    $query->where('name', $position);
                                                })
                                                ->where('branch_id', $branch->id)
                                                ->first();

                $required = $required_employee ? $required_employee->quantity : null;

                $existing = EmployeeMasterData::whereDate('date_employed', '<=', $asOfLastDayLastMonth)
                                              ->whereDate('date_resigned', '>', $asOfLastDayLastMonth)
                                              ->get()
                                              ->count();

                $deployed = EmployeeMasterData::whereDate('date_employed', '>=', $firstOfMonth)
                                              ->whereDate('date_employed', '<=', $asOf)
                                              ->get()
                                              ->count();

                $resigned = EmployeeMasterData::whereDate('date_resigned', '>=', $firstOfMonth)
                                              ->whereDate('date_resigned', '<=', $asOf)
                                              ->get()
                                              ->count();

                $data[$i]['required.' . $position] = $required;
                $data[$i]['deployed.' . $position] = $deployed;
                $data[$i]['res/term.' . $position] = $resigned;
                $data[$i]['existing.' . $position] = $existing;
                $data[$i]['vacant.' . $position] = $required - (($existing + $deployed) - $resigned);
            }
        }

        return collect($data);

    }

    public function headings(): array
    {   
        $column_names = ['Required', 'Deployed', 'Resigned/Terminated', 'Existing', 'Vacant'];
        $headings = ['Branch'];
        
        foreach ($this->positions as $position) {
            foreach ($column_names as $column) {
                $headings[] = $position . '.' .$column;
            }
        }

        return $headings;
    }
}
