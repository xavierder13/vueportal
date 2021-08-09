<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\ProductCategory;
use App\Brand;
use App\Branch;
use App\InventoryReconciliation;
use App\InventoryReconciliationMap;
use DB;
use Validator;
use Auth;
use Excel;
use App\Imports\InventoryReconMapImport;

class InventoryReconciliationController extends Controller
{
    public function index()
    {   
        $user = Auth::user();
        $branches = Branch::all();
        $inventory_reconciliations = InventoryReconciliation::with('user')
                                        ->with('branch')
                                        ->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created, DATE_FORMAT(date_reconciled, '%m/%d/%Y') as date_reconciled"))
                                        ->get();

        return response()->json([
            'inventory_reconciliations' => $inventory_reconciliations,
            'branches' => $branches
        ], 200);
    }

    public function view($inventory_recon_id)
    {   
        $inventory_reconciliation = InventoryReconciliationMap::where('inventory_recon_id', '=', $inventory_recon_id)->get();

        return response()->json(['inventory_reconciliation' => $inventory_reconciliation], 200);
    }

    public function import(Request $request, $branch_id) 
    {   
        $user = Auth::user();

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
                    'file' => 'required|in:xlsx,xls,',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be xlsx/xls.',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
    
            if ($request->file('file')) {
                    
                // $array = Excel::toArray(new ProjectsImport, $request->file('file'));
                $collection = Excel::toCollection(new InventoryReconMapImport(''), $request->file('file'))[0];
                $ctr_collection = count($collection);
                $columns = [
                    'brand',
                    'model',
                    'product_category',
                    'serial',
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
                        '*.brand_.required' => 'Brand is required',
                        '*.model.required' => 'Model is required',
                        '*.product_category.required' => 'Product Category is required',
                        '*.serial.required' => 'Serial is required',
                    ];
            
                    $valid_fields = [
                        '*.brand' => 'required',
                        '*.model' => 'required|',
                        '*.product_category' => 'required',
                        '*.serial' => 'required',
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
                    $inventory_group = 'Admin-Branch';

                    if($user->id !== 1 && $user->can('inventory-audit'))
                    {
                        $inventory_group = 'Audit-Branch';
                    }

                    $inventory_reconciliation = new InventoryReconciliation();
                    $inventory_reconciliation->branch_id = $branch_id;
                    $inventory_reconciliation->user_id = $user->id;
                    $inventory_reconciliation->date_reconciled = null;
                    $inventory_reconciliation->status = 'unreconciled';
                    $inventory_reconciliation->inventory_group = $inventory_group;
                    $inventory_reconciliation->save();

                    $params = [
                        'inventory_group' => $inventory_group,
                        'inventory_type' => 'SAP',
                        'inventory_recon_id' =>  $inventory_reconciliation->id,
                        'user_id' => $user->id,
                    ];

                    // import excel file
                    Excel::import(new InventoryReconMapImport($params), $path);
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

    public function unreconcile_list(Request $request)
    {   
        $user = Auth::user();
        $branch_id = $request->get('branch_id');
        $inventory_group = 'Admin-Branch';

        if($user->id !== 1 && $user->can('inventory-audit'))
        {
            $inventory_group = 'Audit-Branch';
        }

        $unreconcile_list = InventoryReconciliation::with('branch')
                                                   ->where('branch_id', '=', $branch_id)
                                                   ->where('status', '=', 'unreconciled')
                                                   ->where(function($query) use ($inventory_group, $user) {
                                                        if($user->id !== 1)
                                                        {
                                                            $query->where('inventory_group', '=', $inventory_group);
                                                        }
                                                   })
                                                   ->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created"))
                                                   ->get();

        return response()->json(['unreconcile_list' => $unreconcile_list], 200);
    }

    public function reconcile(Request $requests) 
    {

    }
}
