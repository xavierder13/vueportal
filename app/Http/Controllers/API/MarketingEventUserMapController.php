<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\MarketingEvent;
use App\MarketingEventUserMap;
use App\User;
use Validator;
use DB;

class MarketingEventUserMapController extends Controller
{
    public function store(Request $request)
    {
        
        $rules = [
            'marketing_event_id.required' => 'Marketing Event ID is required',
            'marketing_event_id.integer' => 'Marketing Event ID must be integer',
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be integer',
            'access_level.required' => 'Access Level is required',
            'access_level.integer' => 'Access Level must be integer',
        ];

        $validator = Validator::make($request->all(),[
            'marketing_event_id' => 'required|integer',
            'user_id' => 'required|integer',
            'access_level' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $approver = new MarketingEventUserMap();
        $approver->marketing_event_id = $request->get('marketing_event_id');
        $approver->user_id = $request->get('user_id');
        $approver->access_level = $request->get('access_level');
        $approver->save();

        $approver_id = $approver->id;

        $approver = MarketingEventUserMap::with('user')->where('id', '=', $approver_id)->first();

        $max_access_level = $this->max_access_level($request->get('marketing_event_id'));
        
        return response()->json(['success' => 'Record has been added', 'approver' => $approver, 'max_access_level' => $max_access_level], 200);
    }

    public function edit($marketing_event_id)
    {   
        // $marketing_event_id = $request->get('marketing_event_id');

        // $access_module = AccessModule::find($access_module_id);

        // //if record is empty then display error page
        // if(empty($access_module->id))
        // {
        //     return abort(404, 'Not Found');
        // }
        
        // return response()->json(['access_module' => $access_module], 200);

    }

    public function update(Request $request, $approver_id)
    {
        

        $rules = [
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be integer',
            'access_level.required' => 'Access Level is required',
            'access_level.integer' => 'Access Level must be integer',
        ];

        $validator = Validator::make($request->all(),[
            'user_id' => 'required|integer',
            'access_level' => 'required|integer'
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $approver = MarketingEventUserMap::find($approver_id);
        $approver->user_id = $request->get('user_id');
        $approver->access_level = $request->get('access_level');
        $approver->save();

        $approver = MarketingEventUserMap::with('user')
                                      ->with('user.branch')
                                      ->where('id', '=', $approver_id)
                                      ->first();
                                      
        $max_access_level = $this->max_access_level($approver->marketing_event_id);

        return response()->json(['success' => 'Record has been added', 'approver' => $approver, 'max_access_level' => $max_access_level], 200);
    }

    public function delete(Request $request)
    {   
        $approver_id = $request->get('approver_id');
        $approver = MarketingEventUserMap::find($approver_id);
        
        //if record is empty then display error page
        if(empty($approver->id))
        {
            return abort(404, 'Not Found');
        }

        $approver->delete();

        $max_access_level = $this->max_access_level($approver->marketing_event_id);

        return response()->json(['success' => 'Record has been deleted', 'max_access_level' => $max_access_level], 200);
    }

    public function max_access_level($marketing_event_id)
    {
        $max_access_level = MarketingEventUserMap::where('marketing_event_id', '=', $marketing_event_id)->max('access_level');

        return $max_access_level;
    }
}
