<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Employee;
use App\Branch;
use Carbon\Carbon;
use Validator;
use DB;
use Excel;
use App\Imports\EmployeesImport;
use App\Exports\EmployeesExport;


class EmployeeController extends Controller
{
    public function index()
    {
     
        $branches = DB::table('branches')
                      ->leftJoin('employees', 'branches.id', '=', 'employees.branch_id')
                      ->select('branches.id', 'branches.name', DB::raw("DATE_FORMAT(max(employees.created_at), '%m/%d/%Y') as last_upload"))
                      ->groupBy('branches.id', 'branches.name')
                      ->get();

        return response()->json(['branches' => $branches], 200);
    }

    public function list_view($branch_id)
    {
        $employees = Employee::with('branch')
                             ->where('branch_id', '=', $branch_id)
                             ->select(DB::raw("*, DATE_FORMAT(dob, '%m/%d/%Y') as birth_date, DATE_FORMAT(date_employed, '%m/%d/%Y') as date_employed, 
                                                CONCAT(CAST((TIMESTAMPDIFF(DAY, date_employed, date_format(NOW(),'%Y-%m-%d')) / 365) AS UNSIGNED), ' years(s) ',
                                                CAST(((TIMESTAMPDIFF(DAY, date_employed, date_format(NOW(),'%Y-%m-%d')) % 365) / 30) AS UNSIGNED), ' month(s) ',
                                                ((TIMESTAMPDIFF(DAY, date_employed, date_format(NOW(),'%Y-%m-%d')) % 365) % 30), ' day(s)')  as length_of_service"
                                            )
                                    )
                             ->get();  

        return response()->json(['employees' => $employees], 200);
    }

    public function create()
    {

    }

    public function store(Request $request)
    { 
        $rules = [
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'employee_code.required' => 'Employee Code is required',
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'birth_date.required' => 'Birth Date is required',
            'birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'address.required' => 'Address is required',
            'contact.required' => 'Contact is required',
            'rank.required' => 'Rank is required',
            'department.required' => 'Department is required',
            'cost_center_code.required' => 'Cost Center Code is required',
            'job_description.required' => 'Job Description is required',
            'date_employed.required' => 'Date Employed is required',
            'date_employed.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'gender.required' => 'Gender is required',
            'civil_status.required' => 'Civil Status is required',
            'tax_status.required' => 'Tax Status is required',
            'tin_no.required' => 'TIN No. is required',
            'tax_branch_code.required' => 'Tax Branch Code is required',
            'pagibig_no.required' => 'Pag-IBIG No. is required',
            'philhealth_no.required' => 'PhilHealth No. is required',
            'sss_no.required' => 'SSS No. is required',
            'time_schedule.required' => 'Time Schedule is required',
            'restday.required' => 'Rest Day is required',
            'educ_attain.required' => 'Educational Attainment is required',
            'school_attended.required' => 'School Attended is required',
            'course.required' => 'Course is required',
        ];

        $valid_fields = [
            'branch_id' => 'required|integer',
            'employee_code' => 'required',
            'last_name' => 'required',
            'first_name' => 'required',
            'birth_date' => 'required|date_format:Y-m-d',
            'address' => 'required',
            'contact' => 'required',
            'email' => 'required',
            'class' => 'required',
            'rank' => 'required',
            'department' => 'required',
            'cost_center_code' => 'required',
            'job_description' => 'required',
            'date_employed' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'civil_status' => 'required',
            'tax_status' => 'required',
            'tin_no' => 'required',
            'tax_branch_code' => 'required',
            'pagibig_no' => 'required',
            'philhealth_no' => 'required',
            'sss_no' => 'required',
            'time_schedule' => 'required',
            'restday' => 'required',
            'educ_attain' => 'required',
            'school_attended' => 'required',
            'course' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $employee = new Employee();
        $employee->branch_id = $request->get('branch_id');
        $employee->employee_code = $request->get('employee_code');
        $employee->last_name = $request->get('last_name');
        $employee->first_name = $request->get('first_name');
        $employee->dob = $request->get('birth_date');
        $employee->address = $request->get('address');
        $employee->contact = $request->get('contact');
        $employee->email = $request->get('email');
        $employee->class = $request->get('class');
        $employee->rank = $request->get('rank');
        $employee->department = $request->get('department');
        $employee->cost_center_code = $request->get('cost_center_code');
        $employee->job_description = $request->get('job_description');
        $employee->date_employed = $request->get('date_employed');
        $employee->gender = $request->get('gender');
        $employee->civil_status = $request->get('civil_status');
        $employee->tax_status = $request->get('tax_status');
        $employee->tin_no = $request->get('tin_no');
        $employee->tax_branch_code = $request->get('tax_branch_code');
        $employee->pagibig_no = $request->get('pagibig_no');
        $employee->philhealth_no = $request->get('philhealth_no');
        $employee->sss_no = $request->get('sss_no');
        $employee->time_schedule = $request->get('time_schedule');
        $employee->restday = $request->get('restday');
        $employee->educ_attain = $request->get('educ_attain');
        $employee->school_attended = $request->get('school_attended');
        $employee->course = $request->get('course');
        $employee->save();

        $branch = Branch::find($request->get('branch_id'));

        return response()->json([
            'success' => 'Record has successfully added', 
            'employee' => $employee, 
            'branch' => $branch
        ], 200);

    }

    public function edit($employee_id)
    {
        $employee = Employee::find($employee_id);
       
        //if record is empty then display error page
        if(empty($employee->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json([
            'employee' => $employee,
        ], 200);

    }

    public function update(Request $request, $employee_id)
    {  
        $rules = [
            'employee_code.required' => 'Employee Code is required',
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'birth_date.required' => 'Birth Date is required',
            'birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'address.required' => 'Address is required',
            'contact.required' => 'Contact is required',
            'rank.required' => 'Rank is required',
            'department.required' => 'Department is required',
            'cost_center_code.required' => 'Cost Center Code is required',
            'job_description.required' => 'Job Description is required',
            'date_employed.required' => 'Date Employed is required',
            'date_employed.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'gender.required' => 'Gender is required',
            'civil_status.required' => 'Civil Status is required',
            'tax_status.required' => 'Tax Status is required',
            'tin_no.required' => 'TIN No. is required',
            'tax_branch_code.required' => 'Tax Branch Code is required',
            'pagibig_no.required' => 'Pag-IBIG No. is required',
            'philhealth_no.required' => 'PhilHealth No. is required',
            'sss_no.required' => 'SSS No. is required',
            'time_schedule.required' => 'Time Schedule is required',
            'restday.required' => 'Rest Day is required',
            'educ_attain.required' => 'Educational Attainment is required',
            'school_attended.required' => 'School Attended is required',
            'course.required' => 'Course is required',
        ];

        $valid_fields = [
            'employee_code' => 'required',
            'last_name' => 'required',
            'first_name' => 'required',
            'birth_date' => 'required|date_format:Y-m-d',
            'address' => 'required',
            'contact' => 'required',
            'email' => 'required',
            'class' => 'required',
            'rank' => 'required',
            'department' => 'required',
            'cost_center_code' => 'required',
            'job_description' => 'required',
            'date_employed' => 'required|date_format:Y-m-d',
            'gender' => 'required',
            'civil_status' => 'required',
            'tax_status' => 'required',
            'tin_no' => 'required',
            'tax_branch_code' => 'required',
            'pagibig_no' => 'required',
            'philhealth_no' => 'required',
            'sss_no' => 'required',
            'time_schedule' => 'required',
            'restday' => 'required',
            'educ_attain' => 'required',
            'school_attended' => 'required',
            'course' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $employee = Employee::find($employee_id);
        $employee->employee_code = $request->get('employee_code');
        $employee->last_name = $request->get('last_name');
        $employee->first_name = $request->get('first_name');
        $employee->dob = $request->get('birth_date');
        $employee->address = $request->get('address');
        $employee->contact = $request->get('contact');
        $employee->email = $request->get('email');
        $employee->class = $request->get('class');
        $employee->rank = $request->get('rank');
        $employee->department = $request->get('department');
        $employee->cost_center_code = $request->get('cost_center_code');
        $employee->job_description = $request->get('job_description');
        $employee->date_employed = $request->get('date_employed');
        $employee->gender = $request->get('gender');
        $employee->civil_status = $request->get('civil_status');
        $employee->tax_status = $request->get('tax_status');
        $employee->tin_no = $request->get('tin_no');
        $employee->tax_branch_code = $request->get('tax_branch_code');
        $employee->pagibig_no = $request->get('pagibig_no');
        $employee->philhealth_no = $request->get('philhealth_no');
        $employee->sss_no = $request->get('sss_no');
        $employee->time_schedule = $request->get('time_schedule');
        $employee->restday = $request->get('restday');
        $employee->educ_attain = $request->get('educ_attain');
        $employee->school_attended = $request->get('school_attended');
        $employee->course = $request->get('course');
        $employee->save();

        return response()->json(['success' => 'Record has been updated', 'employee' => $employee], 200);
    } 


    public function import_employee(Request $request, $branch_id) 
    {   

        try {
            $file_extension = '';
            $path = '';
            if($request->file('file'))
            {   
                $path = $request->file('file')->getRealPath();
                $file_extension = $request->file('file')->getClientOriginalExtension();
            }

            $validator = Validator::make(
                [
                    'file' => strtolower($file_extension),
                ],
                [
                    'file' => 'required|in:xlsx,xls,ods,odsx',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be xlsx/xls/ods/odsx.',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
    
            if ($request->file('file')) {
                    
                // $array = Excel::toArray(new ProjectsImport, $request->file('file'));
                $collection = Excel::toCollection(new EmployeesImport($branch_id), $request->file('file'))[0];
                $ctr_collection = count($collection);
                $columns = [
                    'employee_code',
                    'last_name',
                    'first_name',
                    'middle_name',
                    'birth_date',
                    'address',
                    'contact',
                    'email',
                    'class',
                    'rank',
                    'department',
                    'cost_center_code',
                    'job_description',
                    'date_employed',
                    'gender',
                    'civil_status',
                    'tax_status',
                    'tin_no',
                    'tax_branch_code',
                    'pagibig_no',
                    'philhealth_no',
                    'sss_no',
                    'time_schedule',
                    'restday',
                    'educ_attain',
                    'school_attended',
                    'course'
                ]; 

                $collection_errors = [];
                $collection_column_errors = [];
                $fields = [];    

                // if no. of columns did not match
                if(count($collection[0]) <> count($columns))
                {
                    $collection_column_errors[] = 'Number of columns did not match';    
                    return response()->json(['error_column' => $collection_column_errors], 200);                                
                }
                elseif($ctr_collection > 1)
                {   
               
                    for($x=0; $ctr_collection > $x; $x++)
                    {   
                        for($y=0; count($collection[$x]) > $y; $y++)
                        {
                            if($x == 0)
                            {   
                                
                                // if column name did not match
                                if($collection[$x][$y] != $columns[$y])
                                {
                                    $collection_column_errors[] =  'Invalid column name "'. $collection[$x][$y] . '" on column index ' . $y . '. Column name must be "' . $columns[$y] . '"';
                                } 
                            }  
                            else
                            {   
                                $fields[$x - 1][$columns[$y]] = $collection[$x][$y];
                            }
                        }

                        // if columns has errors
                        if(count($collection_column_errors))
                        {
                            return response()->json(['error_column' => $collection_column_errors], 200);
                        }

                    } 

                    $rules = [
                        '*.employee_code.required' => 'Employee Code is required',
                        '*.last_name.required' => 'Lastname is required',
                        '*.first_name.required' => 'Firstname is required',
                        '*.birth_date.required' => 'Birth Date is required',
                        '*.birth_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.address.required' => 'Address is required',
                        '*.contact.required' => 'Contact is required',
                        '*.rank.required' => 'Rank is required',
                        '*.department.required' => 'Department is required',
                        '*.cost_center_code.required' => 'Cost Center Code is required',
                        '*.job_description.required' => 'Job Description is required',
                        '*.date_employed.required' => 'Date Employed is required',
                        '*.date_employed.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.gender.required' => 'Gender is required',
                        '*.civil_status.required' => 'Civil Status is required',
                        '*.tax_status.required' => 'Tax Status is required',
                        '*.tin_no.required' => 'TIN No. is required',
                        '*.tax_branch_code.required' => 'Tax Branch Code is required',
                        '*.pagibig_no.required' => 'Pag-IBIG No. is required',
                        '*.philhealth_no.required' => 'PhilHealth No. is required',
                        '*.sss_no.required' => 'SSS No. is required',
                        '*.time_schedule.required' => 'Time Schedule is required',
                        '*.restday.required' => 'Rest Day is required',
                        '*.educ_attain.required' => 'Educational Attainment is required',
                        '*.school_attended.required' => 'School Attended is required',
                        '*.course.required' => 'Course is required',
                    ];
            
                    $valid_fields = [
                        '*.employee_code' => 'required',
                        '*.last_name' => 'required',
                        '*.first_name' => 'required',
                        '*.birth_date' => 'required|date_format:Y-m-d',
                        '*.address' => 'required',
                        '*.contact' => 'required',
                        '*.email' => 'required',
                        '*.class' => 'required',
                        '*.rank' => 'required',
                        '*.department' => 'required',
                        '*.cost_center_code' => 'required',
                        '*.job_description' => 'required',
                        '*.date_employed' => 'required|date_format:Y-m-d',
                        '*.gender' => 'required',
                        '*.civil_status' => 'required',
                        '*.tax_status' => 'required',
                        '*.tin_no' => 'required',
                        '*.tax_branch_code' => 'required',
                        '*.pagibig_no' => 'required',
                        '*.philhealth_no' => 'required',
                        '*.sss_no' => 'required',
                        '*.time_schedule' => 'required',
                        '*.restday' => 'required',
                        '*.educ_attain' => 'required',
                        '*.school_attended' => 'required',
                        '*.course' => 'required',
                    ];
                    
                    $validator = Validator::make($fields, $valid_fields, $rules);  
            
                    if($validator->fails())
                    {
                        $collection_errors =  $validator->errors();
                    }
                    
                }
                else
                {   
                    // if file has no row data
                    return response()->json(['error_empty' => 'File is Empty'], 200);
                }
                
                // if row data has errors
                if(count($collection_errors))
                {
                    return response()->json(['error_row_data' => $collection_errors, 'field_values' => $fields], 200);
                }
                else
                {   
                    // import excel file
                    Excel::import(new EmployeesImport($branch_id), $path);
                }
                    
                return response()->json(['success' => 'Record has successfully imported'], 200);
            }
            else
            {
                return response()->json(['error_empty' => 'File is empty'], 200);
            }
          
        } catch (\Exception $e) {
          
            return response()->json(['error' => $e->getMessage()], 200);
        }
        
    }

    public function export_employee($branch_id)
    {   
        return Excel::download(new EmployeesExport($branch_id), 'employees.xls');
    }

    public function delete(Request $request)
    {   
        
        $branch_id = $request->get('branch_id');

        $employees = DB::table('employees')
                        ->where(function($query) use ($branch_id){
                            if($branch_id <> 0)
                            {
                                $query->where('employees.branch_id', '=', $branch_id);
                            }
                        });
        
        if($request->get('clear_list'))
        {   
            
            $employees = DB::table('employees')
                      ->where('branch_id', '=', $request->get('branch_id'));
            
            if(!$employees->count('id'))
            {
                return response()->json('No record found', 200);
            }
            else
            {
                $employees->delete();
            }

        }
        else
        {
            
            $employee = Employee::find($request->get('employee_id'));

            //if record is empty then display error page
            if(empty($employee->id))
            {
                return abort(404, 'Not Found');
            }

            $employee->delete();
        }

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}
