<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\ProductCategory;
use App\Brand;
use App\Branch;
use App\WarehouseCode;
use App\InventoryReconciliation;
use App\InventoryReconciliationMap;
use App\SapDatabase;
use App\Exports\InventoryDiscrepancy;
use App\Exports\InventoryBreakdown;
use DB;
use Validator;
use Auth;
use Excel;
use Crypt;
use Config;
use App\Imports\InventoryReconMapImport;
use Carbon\Carbon;

class InventoryReconciliationController extends Controller
{
    public function index()
    {   

        // Config::set('database.connections.progtbl', array(
        //     'driver' => 'sqlsrv',
        //     'host' => '192.168.1.13',
        //     'port' => '1433',
        //     'database' => 'ReportsFinance',
        //     'username' => 'sapprog105',
        //     'password' => '105*Prog',   
        // ));

        // $progtble = DB::connection('progtbl')->select("select * from [@PROGTBL] where U_Branch1 = 'Alaminos'");
        
        $databases = SapDatabase::all();
        $user = Auth::user();
        $branches = Branch::with(['inventory_reconciliations' => function($query) use ($user){
                            $query->select(
                                DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created, 
                                         DATE_FORMAT(date_reconciled, '%m/%d/%Y') as date_reconciled, 
                                         (select name from users where id = user_id) as user")
                            )
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
                                        $query->where('branch_id', '=', $user->branch_id)
                                              ->where('inventory_group', '=', 'Admin-Branch');
                                    }
                                }
                            });
                          }])
                          ->orderBy('name')->get();
                                      
        return response()->json([
            'branches' => $branches,
            'databases' => $databases
        ], 200);
    }

    public function discrepancy($inventory_recon_id)
    {   
        return response()->json($this->getDiscrepancy($inventory_recon_id), 200);
    }

    public function getDiscrepancy($inventory_recon_id)
    {
        $reconciliation = InventoryReconciliation::select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created, DATE_FORMAT(date_reconciled, '%m/%d/%Y') as date_reconciled"))
                                                 ->with('branch')
                                                 ->with('user')
                                                 ->with('user.position')
                                                 ->find($inventory_recon_id);
        $date_reconciled = '';

        if($reconciliation)
        {
            $date_reconciled = Carbon::parse($reconciliation->updated_at)->format('m-d-y');
        }
        
        $invty_recon = InventoryReconciliationMap::where('inventory_recon_id', '=', $inventory_recon_id)->get();
        $product_distinct = InventoryReconciliationMap::distinct()
                                                      ->where('inventory_recon_id', '=', $inventory_recon_id)
                                                      ->orderBy('brand', 'ASC')
                                                      ->orderBy('model', 'ASC')
                                                      ->orderBy('product_category', 'ASC')
                                                      ->get(['brand', 'model', 'product_category']);
        $products = [];

        // exclude all sap generated serial items
        $serialized_items = InventoryReconciliationMap::where('inventory_recon_id', '=', $inventory_recon_id)
                                                      ->where(function($query) {
                                                            $warehouses = WarehouseCode::all();
                                                            foreach ($warehouses as $whse) {
                                                                $query->where('serial', 'not like', '%'.$whse->code.'%');
                                                            }
                                                        })->get();

        foreach ($product_distinct as $product) {
            $sap_discrepancy = [];
            $physical_discrepancy = [];

            $recon = $invty_recon->where('brand', $product['brand'])
                                 ->where('model', $product['model'])
                                 ->where('product_category', $product['product_category']);
            
            $sap_qty = $recon->where('inventory_type', '=', 'SAP')->sum('quantity');
            $physical_qty = $recon->where('inventory_type', '=', 'Physical')->sum('quantity');

            // exclude sap generated serial numbers
            $items = $serialized_items->where('brand', $product['brand'])
                                    ->where('model', $product['model'])
                                    ->where('product_category', $product['product_category']);

            $sap = $items->where('inventory_type', '=', 'SAP');
            $physical = $items->where('inventory_type', '=', 'Physical');

            // SAP product inventory
            foreach ($sap as $value) {
                
                // find the serial from physical_inventory to identify the discrep
                $physical_serial_ctr = $physical->where('serial', $value['serial'])->count();

                // if this product is not in Physical Inventory then get the serial discrepancy
                if($physical_serial_ctr === 0)
                {
                    $sap_discrepancy[] = $value['serial'];
                }
                
            }
            
            // Physical product inventory
            foreach ($physical as $value) {
                
                // find the serial from sap to identify the discrep
                $sap_serial_ctr = $sap->where('serial', $value['serial'])->count();

                // if this product is not in SAP Inventory then get the serial discrepancy
                if($sap_serial_ctr === 0)
                {
                    $physical_discrepancy[] = $value['serial'];
                }

            }

            $products[] = [
                'brand' => $product['brand'],
                'model' => $product['model'],
                'product_category' => $product['product_category'],
                'sap_qty' => $sap_qty,
                'physical_qty' => $physical_qty,
                'qty_diff' => $physical_qty - $sap_qty, // physical - sap quantity
                'sap_discrepancy' => join(', ', $sap_discrepancy),
                'physical_discrepancy' => join(', ', $physical_discrepancy),
            ];

        }
        
        return [
            'products' => $products,
            'date_reconciled' => $date_reconciled,
            'reconciliation' => $reconciliation,
        ];
    }

    public function breakdown($inventory_recon_id)
    {   

        return response()->json($this->getBreakdown($inventory_recon_id), 200);
    }

    public function getBreakdown($inventory_recon_id)
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

        foreach ($product_distinct as $product) {

            $sap_serial_ctr =  $sap_inventory->where('brand', $product['brand'])
                                  ->where('model', $product['model'])
                                  ->where('product_category', $product['product_category'])
                                  ->where('serial', $product['serial']);

            $physical_serial_ctr = $physical_inventory->where('brand', $product['brand'])
                                           ->where('model', $product['model'])
                                           ->where('product_category', $product['product_category'])
                                           ->where('serial', $product['serial']);

            $products[] = [
                'brand' => $product['brand'],
                'model' => $product['model'],
                'product_category' => $product['product_category'],
                'sap_serial' => $sap_serial_ctr ? $product['serial'] : '---',
                'physical_serial' => $physical_serial_ctr ? $product['serial'] : '---',
            ];

        }
        
        return [
            'inventory_reconciliation' => $inventory_reconciliation, 
            'sap_inventory' => $sap_inventory, 
            'physical_inventory' => $physical_inventory,
            'products' => $products,
            'date_reconciled' => $date_reconciled,
            'reconciliation' => $reconciliation,

        ];
    }

    public function import(Request $request) 
    {   
        $user = Auth::user();
        $branch_id = $request->get('branch_id');
        $inventory_group = $request->get('inventory_group');
        $inventory_type = $request->get('inventory_type');

        try {
            $file_extension = '';
            $path = '';
            if($request->file('file'))
            {   
                $path1 = $request->file('file')->store('temp'); 
                $path=storage_path('app').'/'.$path1;  
                // $path = $request->file('file')->getRealPath();
                $file_extension = $request->file('file')->getClientOriginalExtension();
            }

            $validator = Validator::make(
                [
                    'file' => strtolower($file_extension),
                ],
                [
                    'file' => 'required|in:xlxs,xls,',
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
                // $columns = [
                //     'brand',
                //     'model',
                //     'product_category',
                //     'serial',
                // ];
                
                $columns = [
                    'BRAND',
                    'MODEL',
                    'CATEGORY',
                    'SERIAL',
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

                    // $rules = [
                    //     '*.brand.required' => 'Brand is required',
                    //     '*.model.required' => 'Model is required',
                    //     '*.product_category.required' => 'Product Category is required',
                    //     '*.serial.required' => 'Serial is required',
                    // ];

                    $rules = [
                        '*.BRAND.required' => 'Brand is required',
                        '*.MODEL.required' => 'Model is required',
                        '*.CATEGORY.required' => 'Product Category is required',
                        // '*.SERIAL.required' => 'Serial is required',
                    ];
            
                    $valid_fields = [
                        '*.BRAND' => 'required',
                        '*.MODEL' => 'required|',
                        '*.CATEGORY' => 'required',
                        // '*.SERIAL' => 'required',
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
                    $inventory_reconciliation->inventory_type = $inventory_type;
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
                                                   ->where('inventory_type', $request->inventory_type)
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
            // 'products.*.serial.required' => 'Serial is required',
        ];

        $valid_fields = [
            'inventory_recon_id' => 'required|integer',
            'products.*.brand' => 'required',
            'products.*.model' => 'required',
            'products.*.product_category' => 'required',
            // 'products.*.serial' => 'required',
        ];
        
        $validator = Validator::make($request->all(), $valid_fields, $rules);  

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        // scan for duplicate data
        foreach ($products as $product) {
            if($product['serial'])
            {
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
        }
        
        foreach ($products as $product) {

            $qty = $product['quantity'] ? $product['quantity'] : 1;

            $inventory_recon_map = new InventoryReconciliationMap();
            $inventory_recon_map->inventory_recon_id = $inventory_recon_id;
            $inventory_recon_map->user_id = $user->id;
            $inventory_recon_map->inventory_type = 'Physical';
            $inventory_recon_map->brand = $product['brand']['name'];
            $inventory_recon_map->model = $product['model'];
            $inventory_recon_map->product_category = $product['product_category']['name'];
            $inventory_recon_map->serial = $product['serial'];
            $inventory_recon_map->quantity = $qty ;
            $inventory_recon_map->save();
        }

        $inventory_reconciliation = InventoryReconciliation::find($inventory_recon_id);
        $inventory_reconciliation->status = 'reconciled';
        $inventory_reconciliation->date_reconciled = Carbon::now()->format('Y-m-d');
        $inventory_reconciliation->save();
        
        return response()->json(['success' => 'Inventory has beend reconciled'], 200);
        
    } 

    public function sync_inventory_recon(Request $request)
    {   

        try{

            $user = Auth::user();
            $branch_id = $request->get('branch_id');
            $branch = Branch::find($branch_id);
            $inventory_group = $request->get('inventory_group');
            $inventory_type = $request->get('inventory_type');
    
            $database = $request->get('database');
            $db = SapDatabase::where('database', '=', $database)->get()->first();
            $password = Crypt::decrypt($db->password);
            Config::set('database.connections.'.$db->database, array(
                        'driver' => 'sqlsrv',
                        'host' => $db->server,
                        'port' => '1433',
                        'database' => $db->database,
                        'username' => $db->username,
                        'password' => $password,   
                    ));
            
            $operator = $request->inventory_type === 'OVERALL' ? '<>' : '=';

            $inventory_onhand = DB::connection($database)->select("
                SELECT 
                    d.FirmName BRAND, 
                    c.ItemName MODEL,
                    c.FrgnName CATEGORY, 
                    b.IntrSerial SERIAL,
                    cast(1 as numeric(19,2)) as 'Qty'
                FROM 
                OITW a
                    LEFT JOIN OSRI b on (a.ItemCode = b.ItemCode and a.WhsCode = b.WhsCode)
                    INNER JOIN OITM c on a.ItemCode = c.ItemCode
                    INNER JOIN OMRC d on c.FirmCode = d.FirmCode
                    INNER JOIN OWHS e on a.WhsCode = e.WhsCode 
                    INNER JOIN [@PROGTBL] f on UPPER(e.Street) COLLATE DATABASE_DEFAULT = CASE WHEN DB_NAME() = 'ReportsFinance' OR LEFT(DB_NAME(), 7) = 'Addessa' THEN f.U_Branch2 ELSE f.U_Branch1 END
                WHERE a.OnHand <> 0 and b.Status = '0' and f.U_Branch1 = :branch and RIGHT(e.WhsCode, 3) ".$operator." 'RPO'
                ORDER by 1, 2, 3, 4
            ",
            ['branch' => $branch->name]);
    
            if(!count($inventory_onhand))
            {
                return response()->json(['empty' => 'No record found', $db], 200);
            }
    
            $inventory_reconciliation = new InventoryReconciliation();
            $inventory_reconciliation->branch_id = $branch_id;
            $inventory_reconciliation->user_id = $user->id;
            $inventory_reconciliation->date_reconciled = null;
            $inventory_reconciliation->status = 'unreconciled';
            $inventory_reconciliation->inventory_group = $inventory_group;
            $inventory_reconciliation->inventory_type = $inventory_type;
            $inventory_reconciliation->save();
    
    
            foreach ($inventory_onhand as $value) {
                
                InventoryReconciliationMap::create([
                    'inventory_recon_id' => $inventory_reconciliation->id,
                    'user_id' => $user->id,
                    'inventory_type' => 'SAP',
                    'brand' => $value->BRAND,
                    'model' => $value->MODEL,
                    'product_category' => $value->CATEGORY,
                    'serial' => $value->SERIAL,
                    'quantity' => 1,
                ]);
                
            }
    
    
            return response()->json(['success' => 'Record has been synced'], 200);

        } catch (\Exception $e) {
                
            return response()->json(['error' => $e->getMessage()], 200);
        }

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

    public function export_discrepancy($inventory_recon_id)
    {   
        $data = $this->getDiscrepancy($inventory_recon_id);

        return Excel::download(new InventoryDiscrepancy($data['products']), 'InventoryDiscrepancy.xls');
    }

    public function export_breakdown($inventory_recon_id)
    {   
        $data = $this->getBreakdown($inventory_recon_id);

        return Excel::download(new InventoryBreakdown($data['products']), 'InventoryDiscrepancy.xls');
    }
}
