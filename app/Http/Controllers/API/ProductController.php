<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\ProductCategory;
use App\Brand;
use App\Branch;
use App\WarehouseCode;
use App\ProductModel;
use App\SapDatabase;
use App\SapDbBranch;
use App\FileUploadLog;
use App\InventoryReconciliationMap;
use App\Imports\ProductsImport;
use App\Exports\ProductsExport;
use App\Exports\ProductsTemplate;
use DB;
use Validator;
use Auth;
use Excel;
use Config;
use Crypt;

class ProductController extends Controller
{
    public function index(Request $request)
    {   
        $user_can_product_list_all = Auth::user()->can('product-list-all');
        $branches = Branch::with(['file_upload_logs' => function($query){
                                $query->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_uploaded, DATE_FORMAT(docdate, '%m/%d/%Y') as docdate"))
                                    ->where('docname', '=', 'Product List');
                            }])
                            ->where(function($query) use ($user_can_product_list_all){
                                // if user has no permission to view all the branches then select the user's branch only
                                if(!$user_can_product_list_all)
                                {
                                    $query->where('id', '=', Auth::user()->branch_id);
                                }
                            })
                            ->get();
       
        return response()->json(['branches' => $branches], 200);    
    }

    public function list_view(Request $request)
    {   
        $file_upload_log_id = $request->get('file_upload_log_id');
        $file_upload_log = FileUploadLog::with('branch')
                                        ->select(DB::raw("id, branch_id, DATE_FORMAT(docdate, '%m/%d/%Y') as docdate, DATE_FORMAT(created_at, '%m/%d/%Y') as date_uploaded, remarks"))
                                        ->where('id', $file_upload_log_id)
                                        ->first();
        
        $user = Auth::user();

        $products = Product::with('brand')
                           ->with('branch')
                           ->with('user')
                           ->with('product_category')
                           ->where(function($query) use ($user){
                                if(!$user->hasAnyRole('Administrator', 'Audit Admin', 'Inventory Admin'))
                                {
                                    $query->where('user_id', '=', $user->id);
                                }
                           })
                           ->where('file_upload_log_id', '=', $file_upload_log_id)
                           ->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created"))
                        //    ->paginate($request->items_per_page);
                           ->get();

        $brands = Brand::all();
        $branches = Branch::all();
        $product_categories = ProductCategory::all();
        
        return response()->json([
            'products' => $products, 
            'brands' => $brands,
            'branches' => $branches,
            'product_categories' => $product_categories,
            'file_upload_log' => $file_upload_log,
            $file_upload_log_id
        ], 200);
        
    }

    public function scanned_products() 
    {
        $user = Auth::user();

        $products = Product::with('brand')
                           ->with('branch')
                           ->with('user')
                           ->with('product_category')
                           ->where(function($query) use ($user){
                                if(!$user->hasAnyRole('Administrator', 'Audit Admin', 'Inventory Admin'))
                                {
                                    $query->where('user_id', '=', $user->id);
                                }
                           })
                           ->where(function($query){
                                $query->whereNull('file_upload_log_id')
                                      ->orWhere('file_upload_log_id', 0);

                           })
                           ->select(DB::raw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created"))
                           ->get();

        $brands = Brand::all();
        $branches = Branch::all();
        $product_categories = ProductCategory::all();
        
        return response()->json([
            'products' => $products, 
            'brands' => $brands,
            'branches' => $branches,
            'product_categories' => $product_categories,
            'user' => $user,
        ], 200);
    }

    public function search_model(Request $request)
    {           
        
        $product_models = ProductModel::where( function($query) use($request){
            if($request->search)
            {
                $query->where('name', 'like', '%'.$request->search.'%');
            }
        })
        ->orderBy('name', 'asc')
        ->paginate($request->items_per_page);

        return response()->json(['product_models' => $product_models], 200);
    }

    public function search_serial(Request $request){
        $user = Auth::user();
        $inventory_group = $request->get('inventory_group');
        $product = InventoryReconciliationMap::with('inventory_recon')
                                             ->where('serial', '=', $request->get('serial'))
                                             ->whereHas('inventory_recon', function($query) use ($user, $inventory_group) {
                                                
                                                if($user->id !== 1)
                                                {
                                                    if($user->hasRole('Audit Admin'))
                                                    {
                                                        $inventory_group = 'Audit-Branch';
                                                    }
                                                    else
                                                    {
                                                        $inventory_group = 'Admin-Branch';
                                                    }
                                                }

                                                $query->where('status', '=', 'unreconciled')
                                                      ->where(function($q) use ($inventory_group, $user) {
                                                        if($user->id !== 1)
                                                        {
                                                            $q->where('inventory_group', '=', $inventory_group);
                                                        }
                                                      });
                                                
                                             })
                                             ->get()->first();

        return response()->json(['product' => $product], 200);
    }

    public function create()
    {   
        
        $user = Auth::user();
        $brands = Brand::all();
        $branches = Branch::all();
        $product_categories = ProductCategory::all();

        return response()->json([
            'product_categories' => $product_categories,
            'brands' => $brands,
            'branches' => $branches,
            'user' => $user,
        ], 200);
    }

    public function store(Request $request)
    {
        return response()->json(['success' => 'Record has been added'], 200);
        
        $rules = [
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'brand_id.required' => 'Brand field is required',
            'brand_id.integer' => 'Brand must be an integer',
            'model.required' => 'This field is required',
            'product_category_id.required' => 'Product Category field is required',
            'product_category_id.integer' => 'Product Category must be an integer',
            'products.required' => 'Please enter product details'
        ];

        $valid_fields = [
            'branch_id' => 'required|integer',
            'brand_id' => 'required|integer',
            'model' => 'required',
            'product_category_id' => 'required|integer',
        ];

        $scan_mode = $request->get('scan_mode');

        if($scan_mode == 'Multiple Scan')
        {
            $valid_fields['serials'] = 'required';
        }
        else
        {
            $valid_fields['serial'] = 'required';
        }

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {   
            return response()->json($validator->errors(), 200);
        }

        $user = Auth::user();
        $branch_id = $request->get('branch_id');
        $brand_id = $request->get('brand_id');
        $model = $request->get('model');
        $product_category_id = $request->get('product_category_id');
        $serials = [];
        $serial = $request->get('serial');

        $duplicate_serials = [];
        
        // if serials has value
        if($request->get('serials'))
        {
            $serials = $request->get('serials');
            
            foreach($serials as $index => $value)
            {
                foreach($serials as $i => $val)
                {
                    if($value['serial'] == $val['serial'] && $index <> $i)
                    {   
                        $duplicate_serials[]  = $val['serial'];
                    }
                }
            }
        }
        
        // return error if there are duplicate serials from request
        if(count($duplicate_serials))
        {
            return response()->json(['duplicate_serials' => $duplicate_serials], 200);
        }
        
        // get existing products from database
        $existing_products = Product::where('branch_id', '=', $branch_id)
                                ->where('brand_id', '=', $brand_id)
                                ->where('model', '=', $model)
                                ->where(function($query) use ($serials, $serial){
                                    $query->whereIn('serial', $serials)
                                          ->orWhere('serial', '=', $serial);
                                })->get();
         
        if(count($existing_products))
        {
            return response()->json(['existing_products' => $existing_products], 200);
        }
                                
        if($scan_mode == 'Multiple Scan')
        {
            $ctr = count($serials);
            
            for($x=0; $x < $ctr; $x++)
            {

                $product = new Product();
                $product->user_id = $user->id;
                $product->branch_id = $branch_id;
                $product->brand_id = $brand_id;
                $product->model = $model;
                $product->product_category_id = $product_category_id;
                $product->serial = $serials[$x]['serial'];
                $product->quantity = 1;
                $product->save();

            }
        }
        else
        {   
            $product = new Product();
            $product->user_id = $user->id;
            $product->branch_id = $branch_id;
            $product->brand_id = $brand_id;
            $product->model = $model;
            $product->product_category_id = $product_category_id;
            $product->serial = $serial;
            $product->quantity = 1;
            $product->save();
        }
        

        return response()->json(['success' => 'Record has successfully added'], 200);
    }
    
    public function edit($product_id)
    {
        $product_id = $request->get('product_id');

        $product = Product::with('brand')
                          ->where('id', '=', $product_id)
                          ->first();

        //if record is empty then display error page
        if(empty($product->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['product' => $product], 200);
    }

    public function update(Request $request, $product_id)
    {
        $rules = [
            'branch_id.required' => 'Branch is required',
            'branch_id.integer' => 'Branch must be an integer',
            'brand_id.required' => 'Brand field is required',
            'brand_id.integer' => 'Brand must be an integer',
            'model.required' => 'This field is required',
            'product_category_id.required' => 'Product Categ    ory field is required',
            'product_category_id.integer' => 'Product Category must be an integer',
            'products.required' => 'Please enter product details'
        ];

        $valid_fields = [
            'branch_id' => 'required|integer',
            'brand_id' => 'required|integer',
            'model' => 'required',
            'product_category_id' => 'required|integer',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {   
            return response()->json($validator->errors(), 200);
        }


        $user = Auth::user();
        $branch_id = $request->get('branch_id');
        $brand_id = $request->get('brand_id');
        $model = $request->get('model');
        $product_category_id = $request->get('product_category_id');
        $serials = [];
        $serial = $request->get('serial');

        // if auth is not admin then filter per branch
        // if($user->id !== 1)
        // {
        //     $branch_id = $user->branch_id;
        // }

        // get existing products from database
        $existing_products = Product::where('branch_id', '=', $branch_id)
                                ->where('brand_id', '=', $brand_id)
                                ->where('model', '=', $model)
                                ->where(function($query) use ($serials, $serial){
                                    $query->whereIn('serial', $serials)
                                          ->orWhere('serial', '=', $serial);
                                
                                })
                                ->where('id', '<>', $product_id)
                                ->get();
         
        if(count($existing_products))
        {
            return response()->json(['existing_products' => $existing_products], 200);
        }

        $product = Product::find($product_id);
        $product->branch_id = $branch_id;
        $product->brand_id = $brand_id;
        $product->model = $model;
        $product->product_category_id = $product_category_id;
        $product->serial = $serial;
        $product->quantity = 1;
        $product->save();

        $product = Product::with('brand')
                          ->with('branch')
                          ->with('user')
                          ->where('id', '=', $product_id)
                          ->first();

        return response()->json(['success' => 'Record has successfully updated', 'product' => $product], 200);
    }

    public function delete(Request $request)
    {   

        if($request->get('clear_list'))
        {   
            
            $log = FileUploadLog::find($request->file_upload_log_id);
            $file_upload_log_id = $log->id;
            $log->delete();
            $products = DB::table('products')
                      ->where('file_upload_log_id', '=', $file_upload_log_id);
            
            if(!$products->count('id'))
            {
                return response()->json('No record found', 200);
            }
            else
            {
                $products->delete();
            }

        }
        else
        {
            
            $product = Product::find($request->get('product_id'));

            //if record is empty then display error page
            if(empty($product->id))
            {
                return abort(404, 'Not Found');
            }

            $product->delete();
        }

        return response()->json(['success' => 'Record has been deleted'], 200);
    }

    public function import(Request $request, $branch_id)
    {    
        
        $user = Auth::user();
        $whse_codes = WarehouseCode::all()->pluck('code')->toArray();
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
                    'file' => 'required|in:xlxs,xls,ods',
                ], 
                [
                    'file.required' => 'File is required',
                    'file.in' => 'File type must be xlsx/xls/ods.',
                ]
            );  
            
            if($validator->fails())
            {
                return response()->json($validator->errors(), 200);
            }
            
    
            if ($request->file('file')) {
                    
                // $array = Excel::toArray(new ProjectsImport, $request->file('file'));
                $collection = Excel::toCollection(new ProductsImport(''), $request->file('file'))[0];
                $ctr_collection = count($collection);
                
                $columns = [
                    'BRAND',
                    'MODEL',
                    'CATEGORY',
                    'SERIAL',
                    'QUANTITY'
                ]; 

                $collection_errors = [];
                $collection_column_errors = [];
                $duplicates = [];
                $fields = [];

                // if no. of columns did not match
                if(count($collection[0]) <> count($columns))
                {
                    $collection_column_errors[] = 'Number of columns did not match';    
                    return response()->json(['error_column' => $collection_column_errors], 200);                                
                }
                elseif($ctr_collection > 1)
                {   

                    foreach ($collection as $x => $collection1) {

                        $brand = $collection1[0];
                        $model = $collection1[1];
                        $category = $collection1[2];
                        $serial = $collection1[3];
                        $quantity = $collection1[4];

                        for($y=0; count($collection1) > $y; $y++)
                        {
                            if($x == 0)
                            {   
                                
                                // if column name did not match
                                if(strtoupper($collection1[$y]) != $columns[$y])
                                {
                                    $collection_column_errors[] =  'Invalid column name "'. $collection1[$y] . '" on column index ' . $y . '. Column name must be "' . $columns[$y] . '"';
                                } 
                            }  
                            else
                            {   
                                $row_id = $x - 1;
                                $fields[$row_id][$columns[$y]] = $collection1[$y];

                                foreach ($collection as $i => $collection2) {
                                    $brand2 = $collection2[0];
                                    $model2 = $collection2[1];
                                    $category2 = $collection2[2];
                                    $serial2 = $collection2[3];
                                    $quantity2 = $collection2[4];

                                    // check duplicates if serial has value
                                    if($x !== $i && $x > $i && $serial)
                                    {
                                        if($brand === $brand2[0] && //brand
                                           $model === $model2[1] && //model
                                           $category === $category2[2] && //category
                                           $serial === $serial2[3] //serial
                                        ){
                                            $duplicates [$x] = $serial;
                                        }
                                    }
                                }

                                // check serial if sap generated (string serial contains branch warehouse code) else if non sap generated then quantity must be 1
                                if($serial)
                                {
                                    foreach ($whse_codes as $code) {
                                        if(str_contains($serial, $code))
                                        {
                                            $collection_errors[$row_id.'.SERIAL'] = ['SAP generated serial must be empty in Product Excel Template'];
                                        }
                                        else if($quantity > 1)
                                        {
                                            $collection_errors[$row_id.'.SERIAL'] = ['Quantity of non-SAP generated serial must be 1'];
                                        }
                                    }
                                }
                            }   
                        }

                        // if columns has errors
                        if(count($collection_column_errors))
                        {
                            return response()->json(['error_column' => $collection_column_errors], 200);
                        }
                    
                    }

                    // return $sap_generated_serials;

                    $rules = [
                        '*.BRAND.required' => 'Brand is required',
                        '*.MODEL.required' => 'Model is required',
                        '*.CATEGORY.required' => 'Product Category is required',
                        // '*.SERIAL.required' => 'Serial is required',
                        '*.QUANTITY.integer' => 'Quantity must be an integer',

                    ];
            
                    $valid_fields = [
                        '*.BRAND' => 'required',
                        '*.MODEL' => 'required',
                        '*.CATEGORY' => 'required',
                        // '*.SERIAL' => 'required',
                        '*.QUANTITY' => 'nullable|integer',
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
                
                if(count($duplicates))
                {
                    return response()->json(['duplicate_serials' => $duplicates], 200);
                }

                // file upload logs
                $file_upload_log = new FileUploadLog();
                $file_upload_log->branch_id = $branch_id;
                $file_upload_log->docdate = $request->get('docdate');
                $file_upload_log->docname = "Product List";
                $file_upload_log->remarks = $request->get('inventory_type');
                $file_upload_log->save();
                
                
                foreach ($fields as $field) {

                    $qty = $field['QUANTITY'] ? $field['QUANTITY'] : 1;

                    $brand_id = Brand::firstOrCreate(['name' => $field['BRAND'], 'active' => 'Y'])->id;
                    $product_category_id = ProductCategory::firstOrCreate(['name' => $field['CATEGORY'], 'active' => 'Y'])->id;

                    Product::create([
                        'user_id' => $user->id,
                        'branch_id' => $branch_id,
                        'brand_id' => $brand_id,
                        'model' => $field['MODEL'],
                        'product_category_id' => $product_category_id,
                        'serial' => $field['SERIAL'],
                        'quantity' => $qty,
                        'file_upload_log_id' => $file_upload_log->id,
                    ]);
                }
                
                return response()->json(['success' => 'Record has successfully added'], 200);
            }
            else
            {
                return response()->json(['error_empty' => 'File is empty'], 200);
            }
          
        } catch (\Exception $e) {
          
            return response()->json(['error' => $e->getMessage()], 200);
        }
    }

    public function export(Request $request)
    {   

        // $params = ['file_upload_log_id' => $request->get('file_upload_log_id')];
        return Excel::download(new ProductsExport($request), 'products.xls');
    }

    public function template_download(Request $request)
    {   

        try 
        {
            $sap_db_branch = SapDbBranch::with('sap_database')
                                    ->with('branch')
                                    ->where('branch_id', $request->branch_id)->first();

            $db = $sap_db_branch->sap_database;
            $branch = $sap_db_branch->branch->name;

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
                    
            $inventory_onhand = DB::connection($db->database)->select("
                SELECT 
                    distinct
                    d.FirmName BRAND, 
                    c.ItemName MODEL,
                    c.FrgnName CATEGORY, 
                    '' SERIAL,
                    '' QUANTITY
                    
                FROM 
                OITW a
                    LEFT JOIN OSRI b on (a.ItemCode = b.ItemCode and a.WhsCode = b.WhsCode)
                    INNER JOIN OITM c on a.ItemCode = c.ItemCode
                    INNER JOIN OMRC d on c.FirmCode = d.FirmCode
                    INNER JOIN OWHS e on a.WhsCode = e.WhsCode 
                    INNER JOIN [@PROGTBL] f on UPPER(e.Street) COLLATE DATABASE_DEFAULT = CASE WHEN DB_NAME() = 'ReportsFinance' THEN f.U_Branch2 ELSE f.U_Branch1 END
                WHERE a.OnHand <> 0 and b.Status = '0' and f.U_Branch1 = :branch and RIGHT(e.WhsCode, 3) ".$operator." 'RPO'
                ORDER by 1, 2, 3, 4
            ",
            ['branch' => $branch]);

            return Excel::download(new ProductsTemplate($inventory_onhand), 'template.xls');
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 200);
        }

        
    }

    public function serial_number_details (Request $request)
    {   
        try {

            $databases = SapDatabase::all();

            foreach ($databases as $key => $db) {
                
                $password = Crypt::decrypt($db->password);

                Config::set('database.connections.'.$db->database, array(
                    'driver' => 'sqlsrv',
                    'host' => $db->server,
                    'port' => '1433',
                    'database' => $db->database,
                    'username' => $db->username,
                    'password' => $password,   
                ));
    
                $serial = $request->get('serial');

                $data[$db->database] = DB::connection($db->database)
                               ->select("SELECT	
                                            CASE WHEN a.BaseType = N'20' THEN 'GRPO' ELSE 'GOODS ISSUE' END DocType,
                                            CASE WHEN a.BaseType = N'20' THEN MAX(h.DocNum) ELSE MAX(i.DocNum) END DocNum,
                                            CAST(MAX(a.DocDate) as Date) as DocDate,
                                            c.ItemName as Model,
                                            c.FrgnName as ProductCategory,
                                            d.FirmName as Brand,
                                            e.ItmsGrpNam as ItemGroup,
                                            b.IntrSerial as Serial,
                                            j.CardName as Supplier,
                                            f.U_branch1 as Branch,
                                            g.Name as Company,
                                            isnull(MAX(h.Comments), (SELECT Comments from OIGE where DocNum = MAX(i.DocNum))) as Remarks
                                        FROM
                                            SRI1 a
                                            INNER JOIN OSRI b on a.ItemCode = b.ItemCode and a.SysSerial = b.SysSerial
                                            INNER JOIN OITM c on a.ItemCode = c.ItemCode
                                            INNER JOIN OMRC d on c.FirmCode = d.FirmCode
                                            INNER JOIN OITB e on c.ItmsGrpCod = e.ItmsGrpCod
                                            LEFT JOIN [@PROGTBL] f on CASE WHEN LEFT(a.WhsCode, 4) = 'CMLG' THEN 'CAMI' ELSE LEFT(a.WhsCode, 4) END COLLATE database_default = f.Name COLLATE database_default
                                            LEFT JOIN [@ACOMPANY] g on f.U_Company = g.Code
                                            LEFT JOIN OPDN h on a.BaseType = h.ObjType and h.DocEntry = a.BaseEntry
                                            LEFT JOIN OIGE i on a.BaseType = i.ObjType and i.DocEntry = a.BaseEntry
                                            LEFT JOIN OCRD j on c.CardCode = j.CardCode
                                        WHERE a.BaseType in (60, 20) and b.IntrSerial like '%" . $serial . "%'
                                        GROUP BY a.BaseType, c.ItemName, c.FrgnName, d.FirmName, e.ItmsGrpNam, b.IntrSerial, j.CardName, f.U_branch1, g.Name");
            }

            $filtered_data = array_filter($data);
            $database = "";

            $product = [];
            $products = [];
            $databases = [];
            $serials = [];
            $data = [];

            // assing value into $serials - used for distinct serial/eliminate duplicate serials
            foreach ($filtered_data as $key => $row) {

                $databases[] = $key; //database

                foreach ($row as $i => $value) {
                    $serials[] = $value->Serial;
                    $data[] = $value; // insert into $data array all values from nested array ($filtered_data) ; $filtered_data is from different databases
                }

            }

            $distinct_serials = array_unique($serials);

            foreach ($distinct_serials as $key => $serial) {
                $GI_branch = '';
                $GI_company = '';

                foreach ($data as $i => $value) {
                    if($serial === $value->Serial)
                    {   

                        if($value->DocType === 'GRPO')
                        {
                            $product['date_purchase'] = $value->DocDate;
                            $product['grpo_number'] = $value->DocNum;
                            $product['grpo_remarks'] = $value->Remarks;
                        }
                        else {
                            $product['date_issued'] = $value->DocDate;
                            $product['gi_number'] = $value->DocNum;
                            $product['gi_remarks'] = $value->Remarks;
                            $GI_branch = $value->Branch;
                            $GI_company = $value->Company;
                        }

                        $product['branch'] = $GI_branch ? $GI_branch : $value->Branch;
                        $product['company'] = $GI_company ? $GI_company : $value->Company;

                        $product['supplier'] = $value->Supplier;
                        $product['model'] = $value->Model;
                        $product['brand'] = $value->Brand;
                        $product['product_category'] = $value->ProductCategory;
                        $product['item_group'] = $value->ItemGroup;
                        $product['serial'] = $value->Serial;
                        
                    }
                }

                $products[] = $product;
            }

            return response()->json(['databases' => $databases, 'products' => $products, 'filtered_data' => $filtered_data, 'serials' => array_unique($serials)], 200);

            
        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 200);
        }

    }

    public function sync_item_master_data()
    {   

        try {

            // START sync branch warehouses
            $whseArr = [];
            $databases = SapDatabase::where('server', '=', '192.168.1.13')->get();

            foreach ($databases as $key => $db) {
                
                $password = Crypt::decrypt($db->password);

                Config::set('database.connections.'.$db->database, array(
                    'driver' => 'sqlsrv',
                    'host' => $db->server,
                    'port' => '1433',
                    'database' => $db->database,
                    'username' => $db->username,
                    'password' => $password,   
                ));

                $warehouses = DB::connection($db->database)
                    ->select("SELECT distinct U_Branch1 as branch, LEFT(U_WhseCode, 4) as code from [@PROGTBL]");

                foreach ($warehouses as $warehouse) {
                    
                    $ctr = WarehouseCode::where('code', $warehouse->code)   
                                        ->where('branch', $warehouse->branch)
                                        ->count();
                    
                    // if $branch has value and has no whse_code
                    if(!$ctr)
                    {
                        WarehouseCode::create([
                            'branch' => $warehouse->branch,
                            'code' => $warehouse->code,
                        ]);  
                    }
                }    
            }

            // END sync branch warehouses
                 

            $db = SapDatabase::where('database', '=', 'ReportsFinance')->get()->first();

            $password = Crypt::decrypt($db->password);

            Config::set('database.connections.'.$db->database, array(
                'driver' => 'sqlsrv',
                'host' => $db->server,
                'port' => '1433',
                'database' => $db->database,
                'username' => $db->username,
                'password' => $password,   
            ));

            $brands = Brand::all();

            $models = ProductModel::all();

            $categories = ProductCategory::all();

            $createBrandsTemp = DB::connection("ReportsFinance")->unprepared(
                DB::raw("CREATE TABLE #brands_temp (FirmName varchar(250))")
            );

            $createModelsTemp = DB::connection("ReportsFinance")->unprepared(
                DB::raw("CREATE TABLE #models_temp (ItemName varchar(250))")
            );

            $createCategoriesTemp = DB::connection("ReportsFinance")->unprepared(
                DB::raw("CREATE TABLE #categories_temp (FrgnName varchar(250))")
            );

            // remedy for inserting apostrophe (') into database
            foreach ($brands as $key => $value) {

                // explode apostrophe (')
                $firm_name_explode = explode("'", $value->name);
                $firm_name = "";

                // concat explode value with apostrophe (') and add double apostrophe ('')
                foreach ($firm_name_explode as $key => $val) {

                    if((count($firm_name_explode) - 1) > $key)
                    {
                        $firm_name .= $val ."''" ; // add double apostrophe ('')
                    }
                    else
                    {
                        $firm_name .= $val; 
                    }
                    
                }

                if(count($firm_name_explode ) === 1)
                {
                    $firm_name = $value->name;
                }

                DB::connection("ReportsFinance")->insert(
                    "insert into #brands_temp (FirmName) values ('". $firm_name ."')"
                );
                
            }
            
            // remedy for inserting apostrophe (') into database
            foreach ($models as $key => $value) {

                // explode apostrophe (')
                $item_name_explode = explode("'", $value->name);
                $item_name = "";

                // concat explode value with apostrophe (') and add double apostrophe ('')
                foreach ($item_name_explode as $key => $val) {

                    if((count($item_name_explode) - 1) > $key)
                    {
                        $item_name .= $val ."''" ; // add double apostrophe ('')
                    }
                    else
                    {
                        $item_name .= $val; 
                    }
                    
                }

                if(count($item_name_explode ) === 1)
                {
                    $item_name = $value->name;
                }

                DB::connection("ReportsFinance")->insert(
                    "insert into #models_temp (ItemName) values ('". $item_name ."')"
                );
                
            }

            // remedy for inserting apostrophe (') into database
            foreach ($categories as $key => $value) {

                // explode apostrophe (')
                $category_explode = explode("'", $value->name);
                $category = "";

                // concat explode value with apostrophe (') and add double apostrophe ('')
                foreach ($category_explode as $key => $val) {

                    if((count($category_explode) - 1) > $key)
                    {
                        $category .= $val ."''" ; // add double apostrophe ('')
                    }
                    else
                    {
                        $category .= $val; 
                    }
                    
                }

                if(count($item_name_explode ) === 1)
                {
                    $item_name = $value->name;
                }

                DB::connection("ReportsFinance")->insert(
                    "insert into #categories_temp (FrgnName) values ('". $item_name ."')"
                );
                
            }


            $brands_sap = DB::connection("ReportsFinance")
                        ->select("SELECT distinct FirmName as brand from OMRC where FirmName not in (select FirmName from #brands_temp)");

            $models_sap = DB::connection("ReportsFinance")
                        ->select("SELECT distinct ItemName as model from OITM where ItemName not in (select ItemName from #models_temp)");

            $categories_sap = DB::connection("ReportsFinance")
                        ->select("SELECT distinct FrgnName as category from OITM where FrgnName not in (select FrgnName from #categories_temp)");
            

            // if brands_sap models_sap and categories_sap has no record then Item Master Date is up to date
            if(!count($brands_sap) && !count($models_sap) && !count($categories_sap))
            {
                return response()->json(['empty' => 'Item Master Data is up to date'], 200);
            }

            foreach ($brands_sap as $key => $value) {
                Brand::create(['name' => $value->brand, 'active' => 'Y']);
            }

            foreach ($models_sap as $key => $value) {
                ProductModel::create(['name' => $value->model, 'active' => 'Y']);
            }

            foreach ($categories_sap as $key => $value) {
                ProductCategory::create(['name' => $value->category, 'active' => 'Y']);     
            }
            
            return response()->json(['success' => 'Item master Data has been synced'], 200);

        } catch (\Exception $e) {
            
            return response()->json(['error' => $e->getMessage()], 200);
        }
        
    }
}
