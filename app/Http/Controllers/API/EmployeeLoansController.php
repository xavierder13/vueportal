<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeLoans;
use App\Branch;
use Carbon\Carbon;
use Validator;
use DB;
use Excel;
use App\Imports\EmployeeLoansImport;
use App\Exports\EmployeeLoansExport;

class EmployeeLoansController extends Controller
{
    public function index()
    {
     
        $branches = DB::table('branches')
                      ->leftJoin('employee_loans', 'branches.id', '=', 'employee_loans.branch_id')
                      ->select('branches.id', 'branches.name', DB::raw("DATE_FORMAT(max(employee_loans.created_at), '%m/%d/%Y') as last_upload"))
                      ->groupBy('branches.id', 'branches.name')
                      ->get();

        return response()->json(['branches' => $branches], 200);
    }

    public function list_view($branch_id)
    {
        $employee_loans = EmployeeLoans::with('branch')
                             ->where('branch_id', '=', $branch_id)
                             ->select(DB::raw("*, DATE_FORMAT(employee_loans.date_granted, '%m/%d/%Y') as date_granted, 
                                              DATE_FORMAT(employee_loans.period_from, '%m/%d/%Y') as period_from,
                                              DATE_FORMAT(employee_loans.period_to, '%m/%d/%Y') as period_to"))
                             ->get();  

        return response()->json(['employee_loans' => $employee_loans], 200);
    }

    public function create()
    {

    }

    public function store(Request $request)
    { 
      
        $rules = [
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'position.required' => 'Position is required',
            'loan_type.required' => 'Loan Type is required',
            'description.required' => 'Description is required',
            'ref_no.required' => 'Reference No. is required',
            'date_granted.required' => 'Date Granted is required',
            'date_granted.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'principal_loan.required' => 'Principal Loan is required',
            'principal_loan.numeric' => 'Enter a valid value',
            'principal_loan.between' => 'Enter a valid value',
            'interest.required' => 'Interest Loan is required',
            'interest.numeric' => 'Enter a valid value',
            'interest.between' => 'Enter a valid value',
            'total_loan.required' => 'Total Loan is required',
            'total_loan.numeric' => 'Enter a valid value',
            'total_loan.between' => 'Enter a valid value',
            'terms.required' => 'Terms is required',
            'terms.integer' => 'Terms must be an integer',
            'period_from.required' => 'Period From is required',
            'period_from.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'period_to.required' => 'Period To is required',
            'period_to.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'monthly_amortization.required' => 'Monthly Amortization is required',
            'monthly_amortization.numeric' => 'Enter a valid value',
            'monthly_amortization.between' => 'Enter a valid value',
            'total_paid.required' => 'Total Amount Paid is required',
            'total_paid.numeric' => 'Enter a valid value',
            'total_paid.between' => 'Enter a valid value',
            'balance.required' => 'Balance Amount is required',
            'balance.numeric' => 'Enter a valid value',
            'balance.between' => 'Enter a valid value',
            'remaining_term.required' => 'Remaining Term is required',
            'remaining_term.integer' => 'Remaining Term must be an integer',
            'or_number.required' => 'OR Number is required',
        ];

        $valid_fields = [
            'branch_id' => 'required|integer',
            'last_name' => 'required',
            'first_name' => 'required',
            'position' => 'required',
            'loan_type' => 'required',
            'description' => 'required',
            'ref_no' => 'required',
            'date_granted' => 'required|date_format:Y-m-d',
            'principal_loan' => 'required|numeric|between:1, 999999.99',
            'interest' => 'required|numeric|between:1, 999999.99',
            'total_loan' => 'required|numeric|between:1, 999999.99',
            'principal_loan' => 'required|numeric|between:1, 999999.99',
            'terms' => 'required|integer',
            'period_from' => 'required|date_format:Y-m-d',
            'period_to' => 'required|date_format:Y-m-d',
            'monthly_amortization' => 'required|numeric|between:1, 999999.99',
            'total_paid' => 'required|numeric|between:1, 999999.99',
            'balance' => 'required|numeric|between:1, 999999.99',
            'remaining_term' => 'required|integer',
            'or_number' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $employee_loans = new EmployeeLoans();
        $employee_loans->branch_id = $request->get('branch_id');
        $employee_loans->last_name = $request->get('last_name');	
        $employee_loans->first_name = $request->get('first_name');	
        $employee_loans->middle_name = $request->get('middle_name');	
        $employee_loans->position = $request->get('position');
        $employee_loans->loan_type = $request->get('loan_type');	
        $employee_loans->description = $request->get('description');
        $employee_loans->ref_no = $request->get('ref_no');
        $employee_loans->date_granted = $request->get('date_granted');	
        $employee_loans->principal_loan = $request->get('principal_loan');
        $employee_loans->interest = $request->get('interest');	
        $employee_loans->total_loan = $request->get('total_loan');	
        $employee_loans->terms = $request->get('terms');	
        $employee_loans->period_from = $request->get('period_from');	
        $employee_loans->period_to = $request->get('period_to');	
        $employee_loans->monthly_amortization =	$request->get('monthly_amortization');
        $employee_loans->total_paid = $request->get('total_paid');
        $employee_loans->balance = $request->get('balance');	
        $employee_loans->remaining_term = $request->get('remaining_term');
        $employee_loans->or_number = $request->get('or_number');
        $employee_loans->save();

        $branch = Branch::find($request->get('branch_id'));

        return response()->json([
            'success' => 'Record has successfully added', 
            'employee_loans' => $employee_loans, 
            'branch' => $branch
        ], 200);

    }

    public function edit($employee_loans_id)
    {
        $employee_loans = EmployeeLoans::find($employeeemployee_loans_id_id);
       
        //if record is empty then display error page
        if(empty($employee_loans->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json([
            'employee_loans' => $employee_loans,
        ], 200);

    }

    public function update(Request $request, $employee_loans_id)
    {  

        $rules = [
            'last_name.required' => 'Lastname is required',
            'first_name.required' => 'Firstname is required',
            'position.required' => 'Position is required',
            'loan_type.required' => 'Loan Type is required',
            'description.required' => 'Description is required',
            'ref_no.required' => 'Reference No. is required',
            'date_granted.required' => 'Date Granted is required',
            'date_granted.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'principal_loan.required' => 'Principal Loan is required',
            'principal_loan.numeric' => 'Enter a valid value',
            'principal_loan.between' => 'Enter a valid value',
            'interest.required' => 'Interest Loan is required',
            'interest.numeric' => 'Enter a valid value',
            'interest.between' => 'Enter a valid value',
            'total_loan.required' => 'Total Loan is required',
            'total_loan.numeric' => 'Enter a valid value',
            'total_loan.between' => 'Enter a valid value',
            'terms.required' => 'Terms is required',
            'terms.integer' => 'Terms must be an integer',
            'period_from.required' => 'Period From is required',
            'period_from.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'period_to.required' => 'Period To is required',
            'period_to.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'monthly_amortization.required' => 'Monthly Amortization is required',
            'monthly_amortization.numeric' => 'Enter a valid value',
            'monthly_amortization.between' => 'Enter a valid value',
            'total_paid.required' => 'Total Amount Paid is required',
            'total_paid.numeric' => 'Enter a valid value',
            'total_paid.between' => 'Enter a valid value',
            'balance.required' => 'Balance Amount is required',
            'balance.numeric' => 'Enter a valid value',
            'balance.between' => 'Enter a valid value',
            'remaining_term.required' => 'Remaining Term is required',
            'remaining_term.integer' => 'Remaining Term must be an integer',
            'or_number.required' => 'OR Number is required',
        ];

        $valid_fields = [
            'last_name' => 'required',
            'first_name' => 'required',
            'position' => 'required',
            'loan_type' => 'required',
            'description' => 'required',
            'ref_no' => 'required',
            'date_granted' => 'required|date_format:Y-m-d',
            'principal_loan' => 'required|numeric|between:1, 999999.99',
            'interest' => 'required|numeric|between:1, 999999.99',
            'total_loan' => 'required|numeric|between:1, 999999.99',
            'principal_loan' => 'required|numeric|between:1, 999999.99',
            'terms' => 'required|integer',
            'period_from' => 'required|date_format:Y-m-d',
            'period_to' => 'required|date_format:Y-m-d',
            'monthly_amortization' => 'required|numeric|between:1, 999999.99',
            'total_paid' => 'required|numeric|between:1, 999999.99',
            'balance' => 'required|numeric|between:1, 999999.99',
            'remaining_term' => 'required|integer',
            'or_number' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $employee_loans = EmployeeLoans::find($employee_loans_id);
        $employee_loans->last_name = $request->get('last_name');	
        $employee_loans->first_name = $request->get('first_name');	
        $employee_loans->middle_name = $request->get('middle_name');	
        $employee_loans->position = $request->get('position');
        $employee_loans->loan_type = $request->get('loan_type');	
        $employee_loans->description = $request->get('description');
        $employee_loans->ref_no = $request->get('ref_no');
        $employee_loans->date_granted = $request->get('date_granted');	
        $employee_loans->principal_loan = $request->get('principal_loan');
        $employee_loans->interest = $request->get('interest');	
        $employee_loans->total_loan = $request->get('total_loan');	
        $employee_loans->terms = $request->get('terms');	
        $employee_loans->period_from = $request->get('period_from');	
        $employee_loans->period_to = $request->get('period_to');	
        $employee_loans->monthly_amortization =	$request->get('monthly_amortization');
        $employee_loans->total_paid = $request->get('total_paid');
        $employee_loans->balance = $request->get('balance');	
        $employee_loans->remaining_term = $request->get('remaining_term');
        $employee_loans->or_number = $request->get('or_number');
        $employee_loans->save();

        return response()->json(['success' => 'Record has been updated', 'employee_loans' => $employee_loans], 200);
    } 


    public function import_loans(Request $request, $branch_id) 
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
                    
                $collection = Excel::toCollection(new EmployeeLoansImport($branch_id), $request->file('file'))[0];
                $ctr_collection = count($collection);
                $columns = [
                    'last_name',	
                    'first_name',	
                    'middle_name',	
                    'position',	
                    'loan_type',	
                    'description',
                    'ref_no',
                    'date_granted',	
                    'principal_loan',	
                    'interest',	
                    'total_loan',	
                    'terms',	
                    'period_from',	
                    'period_to',	
                    'monthly_amortization',	
                    'total_paid',	
                    'balance',	
                    'remaining_term',	
                    'or_number',	
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
                        '*.last_name.required' => 'Lastname is required',
                        '*.first_name.required' => 'Firstname is required',
                        '*.position.required' => 'Position is required',
                        '*.loan_type.required' => 'Loan Type is required',
                        '*.description.required' => 'Description is required',
                        '*.ref_no.required' => 'Reference No. is required',
                        '*.date_granted.required' => 'Date Granted is required',
                        '*.date_granted.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.principal_loan.required' => 'Principal Loan is required',
                        '*.principal_loan.numeric' => 'Enter a valid value',
                        '*.principal_loan.between' => 'Enter a valid value',
                        '*.interest.required' => 'Interest Loan is required',
                        '*.interest.numeric' => 'Enter a valid value',
                        '*.interest.between' => 'Enter a valid value',
                        '*.total_loan.required' => 'Total Loan is required',
                        '*.total_loan.numeric' => 'Enter a valid value',
                        '*.total_loan.between' => 'Enter a valid value',
                        '*.terms.required' => 'Terms is required',
                        '*.terms.integer' => 'Terms must be an integer',
                        '*.period_from.required' => 'Period From is required',
                        '*.period_from.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.period_to.required' => 'Period To is required',
                        '*.period_to.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
                        '*.monthly_amortization.required' => 'Monthly Amortization is required',
                        '*.monthly_amortization.numeric' => 'Enter a valid value',
                        '*.monthly_amortization.between' => 'Enter a valid value',
                        '*.total_paid.required' => 'Total Amount Paid is required',
                        '*.total_paid.numeric' => 'Enter a valid value',
                        '*.total_paid.between' => 'Enter a valid value',
                        '*.balance.required' => 'Balance Amount is required',
                        '*.balance.numeric' => 'Enter a valid value',
                        '*.balance.between' => 'Enter a valid value',
                        '*.remaining_term.required' => 'Remaining Term is required',
                        '*.remaining_term.integer' => 'Remaining Term must be an integer',
                        '*.or_number.required' => 'OR Number is required',
                    ];
            
                    $valid_fields = [
                        '*.last_name' => 'required',
                        '*.first_name' => 'required',
                        '*.position' => 'required',
                        '*.loan_type' => 'required',
                        '*.description' => 'required',
                        '*.ref_no' => 'required',
                        '*.date_granted' => 'required|date_format:Y-m-d',
                        '*.principal_loan' => 'required|numeric|between:1, 999999.99',
                        '*.interest' => 'required|numeric|between:1, 999999.99',
                        '*.total_loan' => 'required|numeric|between:1, 999999.99',
                        '*.principal_loan' => 'required|numeric|between:1, 999999.99',
                        '*.terms' => 'required|integer',
                        '*.period_from' => 'required|date_format:Y-m-d',
                        '*.period_to' => 'required|date_format:Y-m-d',
                        '*.monthly_amortization' => 'required|numeric|between:1, 999999.99',
                        '*.total_paid' => 'required|numeric|between:1, 999999.99',
                        '*.balance' => 'required|numeric|between:1, 999999.99',
                        '*.remaining_term' => 'required|integer',
                        '*.or_number' => 'required',
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
                    Excel::import(new EmployeeLoansImport($branch_id), $path);
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

    public function export_loans($branch_id)
    {   
        return Excel::download(new EmployeeLoansExport($branch_id), 'employee_loans.xls');
    }

    public function delete(Request $request)
    {   
        
        $branch_id = $request->get('branch_id');

        $employee_loans = DB::table('employee_loans')
                        ->where(function($query) use ($branch_id){
                            if($branch_id <> 0)
                            {
                                $query->where('employee_loans.branch_id', '=', $branch_id);
                            }
                        });
        
        if($request->get('clear_list'))
        {   
            
            $employee_loans = DB::table('employee_loans')
                      ->where('branch_id', '=', $request->get('branch_id'));
            
            if(!$employee_loans->count('id'))
            {
                return response()->json('No record found', 200);
            }
            else
            {
                $employee_loans->delete();
            }

        }
        else
        {
            
            $employee_loans = EmployeeLoans::find($request->get('employee_loans_id'));

            //if record is empty then display error page
            if(empty($employee_loans->id))
            {
                return abort(404, 'Not Found');
            }

            $employee_loans->delete();
        }

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}
