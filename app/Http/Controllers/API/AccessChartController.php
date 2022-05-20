<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\AccessModule;
use App\AccessChart;
use App\AccessChartUserMap;
use App\AccessLevel;
use App\User;
use Validator;
use DB;

class AccessChartController extends Controller
{
    public function index()
    {
        $access_charts = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->get();
        
        $access_modules = AccessModule::all();
        $users = User::all();
        
        return response()->json(['access_charts' => $access_charts, 'access_modules' => $access_modules, 'users' => $users], 200);
    }

    public function create()
    {

    }

    public function store(Request $request)
    {
        
        $rules = [
            'name.required' => 'Please enter name',
            'name.unique' => 'Access Chart Name already exists',
            'access_for.required' => 'Access ID is required',
            'access_for.integer' => 'Access ID must be integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:access_charts,name',
            'access_for' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_chart = new AccessChart();
        $access_chart->name = $request->get('name');
        $access_chart->access_for = $request->get('access_for');
        $access_chart->save();
        
        $access_chart_id = $access_chart->id;

        $access_chart = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->where('id', '=', $access_chart_id)
                                    ->first();

        return response()->json(['success' => 'Record has been added', 'access_chart' => $access_chart], 200);
    }

    public function edit($access_chart_id)
    {   
        $access_chart_id = $request->get('access_chart_id');

        $access_module = AccessModule::find($access_module_id);

        //if record is empty then display error page
        if(empty($access_module->id))
        {
            return abort(404, 'Not Found');
        }
        
        return response()->json(['access_module' => $access_module], 200);

    }

    public function update(Request $request, $access_chart_id)
    {
        
        $rules = [
            'name.required' => 'Please enter name',
            'name.unique' => 'Access Chart Name already exists',
            'access_for.required' => 'Access ID is required',
            'access_for.integer' => 'Access ID must be integer',
        ];

        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:access_charts,name,'.$access_chart_id,
            'access_for' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $access_chart = AccessChart::find($access_chart_id);
        $access_chart->name = $request->get('name');
        $access_chart->access_for = $request->get('access_for');
        $access_chart->save();

        $access_chart = AccessChart::with('access_chart_user_maps')
                                    ->with('access_chart_user_maps.user')
                                    ->with('access_chart_user_maps.user.branch')
                                    ->with('access_module')
                                    ->where('id', '=', $access_chart_id)
                                    ->first();

        return response()->json(['success' => 'Record has been added', 'access_chart' => $access_chart], 200);
    }

    public function delete(Request $request)
    {   
        $access_chart_id = $request->get('access_chart_id');
        $access_chart = AccessChart::find($access_chart_id);
        
        //if record is empty then display error page
        if(empty($access_chart->id))
        {
            return abort(404, 'Not Found');
        }

        $access_chart->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }


}
