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
use Carbon\Carbon;

class InventoryReconciliationController extends Controller
{
    public function index()
    {   

        $user = Auth::user();
        $branches = Branch::all();
        $inventory_reconciliations = InventoryReconciliation::with('user')
                                        ->with('branch')
                                        ->where(function($query) use ($user) {
                                            if($user->id !== 1)
                                            {
                                                // if user has role then get all Inventory Reconciliation with inventory_group = 'Audit-Branch'
                                                if($user->hasRole('Audit Admin'))
                                                {
                                                    $query->where('inventory_group', '=', 'Audit-Branch');
                                                }
                                                //if user has role then get all Inventory Reconciliation with inventory_group = 'Admin-Branch'
                                                else if($user->hasRole('Inventory Admin'))
                                                {
                                                    $query->where('inventory_group', '=', 'Admin-Branch');
                                                }
                                                // if user has role Inventory Branch then show record with branch_id = user's branch
                                                else if($user->hasRole('Inventory Branch'))
                                                {
                                                    $query->where('branch_id', '=', $user->branch_id);
                                                }
                                            }
                                        })
                                        ->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created, DATE_FORMAT(date_reconciled, '%m/%d/%Y') as date_reconciled"))
                                        ->get();

        return response()->json([
            'inventory_reconciliations' => $inventory_reconciliations,
            'branches' => $branches
        ], 200);
    }

    public function discrepancy($inventory_recon_id)
    {   
        
        $reconciliation = InventoryReconciliation::with('branch')
                                                 ->with('user')
                                                 ->with('user.position')
                                                 ->find($inventory_recon_id);
        $date_reconciled = '';

        if($reconciliation)
        {
            $date_reconciled = Carbon::parse($reconciliation->updated_at)->format('m-d-y');
        }
        
        $inventory_reconciliation = InventoryReconciliationMap::where('inventory_recon_id', '=', $inventory_recon_id)->get();
        $product_distinct = InventoryReconciliationMap::distinct()
                                                      ->where('inventory_recon_id', '=', $inventory_recon_id)
                                                      ->orderBy('brand', 'ASC')
                                                      ->orderBy('model', 'ASC')
                                                      ->orderBy('product_category', 'ASC')
                                                      ->get(['brand', 'model', 'product_category']);
        $sap_inventory = $inventory_reconciliation->where('inventory_type', '=', 'SAP');
        $physical_inventory = $inventory_reconciliation->where('inventory_type', '=', 'Physical');

        $products = [];
        $sap_discrepancy = [];
        $sap_has_serial = false;
        $physical_discrepancy = [];
        $physical_has_serial = false;
        $ctr1 = 0;
        $ctr2 = 0;

        foreach ($product_distinct as $key => $product) {
            $sap_discrepancy = [];
            $physical_discrepancy = [];
            $ctr1 = 0;
            $ctr2 = 0;

            // SAP product inventory
            foreach ($sap_inventory as $index => $sap) {
                
                // count all products per brand and model
                if(strtoupper($product['brand']) == strtoupper($sap['brand']) && 
                   strtoupper($product['model']) == strtoupper($sap['model'])  && 
                   strtoupper($product['product_category']) == strtoupper($sap['product_category']))
                {
                    $ctr1++;

                    $sap_has_serial = false;
                    // get discrepancy based from SAP vs Physical 
                    foreach ($physical_inventory as $i => $physical) {
                        
                        if(strtoupper($sap['brand']) == strtoupper($physical['brand']) && 
                           strtoupper($sap['model']) == strtoupper($physical['model']) && 
                           strtoupper($sap['product_category']) == strtoupper($physical['product_category']) &&
                           $sap['serial'] == $physical['serial'])
                        {
                            $sap_has_serial = true;
                        }
        
                    }

                    // if this product is not in Physical Inventory then get the serial discrepancy
                    if(!$sap_has_serial)
                    {
                        $sap_discrepancy[] = $sap['serial'];
                    }
                }
            
            }
            
            // Physical product inventory
            foreach ($physical_inventory as $key => $physical) {
                
                // count all items per brand and model
                if(strtoupper($product['brand']) == strtoupper($physical['brand']) && 
                   strtoupper($product['model']) == strtoupper($physical['model']) &&
                   strtoupper($product['product_category']) == strtoupper($physical['product_category']))
                {
                    $ctr2++;

                    $physical_has_serial = false;

                    // get discrepancy based from Physical vs SAP 
                    foreach ($sap_inventory as $i => $sap) {
                        
                        if(strtoupper($sap['brand']) == strtoupper($physical['brand']) && 
                           strtoupper($sap['model']) == strtoupper($physical['model']) && 
                           strtoupper($sap['product_category']) == strtoupper($physical['product_category']) &&
                           $sap['serial'] == $physical['serial'])
                        {
                            $physical_has_serial = true;
                        }
        
                    }

                    // if this product is not in SAP Inventory then get the serial discrepancy
                    if(!$physical_has_serial)
                    {
                        $physical_discrepancy[] = $physical['serial'];
                    }
                }

            }

            $products[] = [
                'brand' => $product['brand'],
                'model' => $product['model'],
                'product_category' => $product['product_category'],
                'sap_qty' => $ctr1,
                'physical_qty' => $ctr2,
                'qty_diff' => $ctr1 - $ctr2,
                'sap_discrepancy' => join(', ', $sap_discrepancy),
                'physical_discrepancy' => join(', ', $physical_discrepancy ),
            ];

        }
        
        return response()->json([
            'inventory_reconciliation' => $inventory_reconciliation, 
            'sap_inventory' => $sap_inventory, 
            'physical_inventory' => $physical_inventory,
            'products' => $products,
            'date_reconciled' => $date_reconciled,
            'reconciliation' => $reconciliation,

        ], 200);
    }

    public function breakdown($inventory_recon_id)
    {   
        
        $reconciliation = InventoryReconciliation::with('branch')
                                                 ->with('user')
                                                 ->with('user.position')
                                                 ->find($inventory_recon_id);
        $date_reconciled = '';

        if($reconciliation)
        {
            $date_reconciled = Carbon::parse($reconciliation->updated_at)->format('m-d-y');
        }
        
        $inventory_reconciliation = InventoryReconciliationMap::where('inventory_recon_id', '=', $inventory_recon_id)->get();
        $product_distinct = InventoryReconciliationMap::distinct()
                                                      ->where('inventory_recon_id', '=', $inventory_recon_id)
                                                      ->orderBy('brand', 'ASC')
                                                      ->orderBy('model', 'ASC')
                                                      ->orderBy('product_category', 'ASC')
                                                      ->orderBy('serial', 'ASC')
                                                      ->get(['brand', 'model', 'product_category', 'serial']);
        $sap_inventory = $inventory_reconciliation->where('inventory_type', '=', 'SAP');
        $physical_inventory = $inventory_reconciliation->where('inventory_type', '=', 'Physical');

        $products = [];
        $sap_has_serial = false;
        $physical_has_serial = false;

        foreach ($product_distinct as $key => $product) {

            $sap_has_serial = false;
            $physical_has_serial = false;

            // SAP product inventory
            foreach ($sap_inventory as $index => $sap) {
                
                if(strtoupper($product['brand']) == strtoupper($sap['brand']) && 
                   strtoupper($product['model']) == strtoupper($sap['model'])  && 
                   strtoupper($product['product_category']) == strtoupper($sap['product_category']) &&
                   strtoupper($product['serial']) == strtoupper($sap['serial']))
                {
                    $sap_has_serial = true;
                }
            
            }
            
            // Physical product inventory
            foreach ($physical_inventory as $key => $physical) {
                
                if(strtoupper($product['brand']) == strtoupper($physical['brand']) && 
                   strtoupper($product['model']) == strtoupper($physical['model']) &&
                   strtoupper($product['product_category']) == strtoupper($physical['product_category']) &&
                   strtoupper($product['serial']) == strtoupper($physical['serial']))
                {   
                    $physical_has_serial = true;
                }

            }

            $products[] = [
                'brand' => $product['brand'],
                'model' => $product['model'],
                'product_category' => $product['product_category'],
                'sap_serial' => $sap_has_serial ? $product['serial'] : '---',
                'physical_serial' => $physical_has_serial ? $product['serial'] : '---',
            ];

        }
        
        return response()->json([
            'inventory_reconciliation' => $inventory_reconciliation, 
            'sap_inventory' => $sap_inventory, 
            'physical_inventory' => $physical_inventory,
            'products' => $products,
            'date_reconciled' => $date_reconciled,
            'reconciliation' => $reconciliation,

        ], 200);
    }

    public function import(Request $request) 
    {   
        $user = Auth::user();
        $branch_id = $request->get('branch_id');
        $inventory_group = $request->get('inventory_group');

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

    public function unreconciled_list(Request $request)
    {   
        $user = Auth::user();
        $branch_id = $request->get('branch_id');
        $inventory_group = $request->get('inventory_group');

        $unreconciled_list = InventoryReconciliation::with('branch')
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

        return response()->json(['unreconciled_list' => $unreconciled_list], 200);
    }

    public function reconcile(Request $request) 
    {         

        $user = Auth::user();
        $products = $request->get('products');
        $inventory_recon_id = $request->get('inventory_recon_id');
        $inventory_group = 'Admin-Branch';

        $rules = [
            'inventory_recon_id.required' => 'Inventory Reconciliation ID is required',
            'inventory_recon_id.integer' => 'Inventory Reconciliation ID must be an integer',
            'products.*.brand_.required' => 'Brand is required',
            'products.*.model.required' => 'Model is required',
            'products.*.product_category.required' => 'Product Category is required',
            'products.*.serial.required' => 'Serial is required',
        ];

        $valid_fields = [
            'inventory_recon_id' => 'required|integer',
            'products.*.brand' => 'required',
            'products.*.model' => 'required',
            'products.*.product_category' => 'required',
            'products.*.serial' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        // scan for duplicate data
        foreach ($products as $key => $product) {
            $inventory_recon_map = InventoryReconciliationMap::where('inventory_type', '=', 'Physical')
                                                 ->where('brand', '=', $product['brand']['name'])
                                                 ->where('model', '=', $product['model'])
                                                 ->where('product_category', '=', $product['product_category']['name'])
                                                 ->where('serial', '=', $product['serial'])
                                                 ->where('inventory_recon_id', '=', $inventory_recon_id)
                                                 ->get();
            if(count($inventory_recon_map))
            {   
                return response()->json(['duplicate' => 'Product exists'], 200);
            }
        }
        
        foreach ($products as $key => $product) {

            $inventory_recon_map = new InventoryReconciliationMap();
            $inventory_recon_map->inventory_recon_id = $inventory_recon_id;
            $inventory_recon_map->user_id = $user->id;
            $inventory_recon_map->inventory_type = 'Physical';
            $inventory_recon_map->brand = $product['brand']['name'];
            $inventory_recon_map->model = $product['model'];
            $inventory_recon_map->product_category = $product['product_category']['name'];
            $inventory_recon_map->serial = $product['serial'];
            $inventory_recon_map->quantity = 1;
            $inventory_recon_map->save();
        }

        $inventory_reconciliation = InventoryReconciliation::find($inventory_recon_id);
        $inventory_reconciliation->status = 'reconciled';
        $inventory_reconciliation->date_reconciled = Carbon::now()->format('Y-m-d');
        $inventory_reconciliation->save();
        
        return response()->json(['success' => 'Record has been saved'], 200);
        
    } 
    
    public function delete(Request $request)
    {   
        $inventory_recon_id = $request->get('inventory_recon_id');
        $inventory = InventoryReconciliation::find($inventory_recon_id);
        
        //if record is empty then display error page
        if(empty($inventory->id))
        {
            return abort(404, 'Not Found');
        }

        $inventory->delete();

        InventoryReconciliationMap::where('inventory_recon_id', '=', $inventory_recon_id)->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function export()
    {
        
    }
}
