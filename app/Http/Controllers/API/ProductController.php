<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Product;
use App\ProductCategory;
use App\Brand;
use App\Branch;
use App\ProductModel;
use DB;
use Validator;
use Auth;
use Excel;
use App\Exports\ProductsExport;

class ProductController extends Controller
{
    public function index()
    {   
        $user = Auth::user();

        $products = Product::with('brand')
                           ->with('branch')
                           ->with('user')
                           ->with('product_category')
                           ->where('user_id', '=', $user->id)
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
        if($user->id !== 1)
        {
            $branch_id = $user->branch_id;
        }

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
            
            $products = DB::table('products')
                      ->where('branch_id', '=', $request->get('branch_id'));
            
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


    public function export($branch_id)
    {   
        return Excel::download(new ProductsExport($branch_id), 'products.xls');
    }
}
