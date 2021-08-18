<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Position;
use Validator;
use DB;

class PositionController extends Controller
{
    public function index()
    {       
        $positions = Position::all();
        return response()->json(['positions' => $positions], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = new Position();
        $position->name = $request->get('name');
        $position->save();

        return response()->json(['success' => 'Record has successfully added', 'position' => $position], 200);
    }


    public function edit(Request $request)
    {   
        $position_id = $request->get('position_id');

        $position = Position::find($position_id);

        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }
        
        // return view('pages.service.edit', compact('service'));
        return response()->json(['position' => $position], 200);

    }


    public function update(Request $request, $position_id)
    {   

        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name,'.$position_id,
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = Position::find($position_id);

        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }

        $position->name = $request->get('name');
        $position->save();


        return response()->json([
            'success' => 'Record has been updated',
            'position' => $position]
        );
    }


    public function delete(Request $request)
    {   
        $position_id = $request->get('position_id');
        $position = Position::find($position_id);
        
        //if record is empty then display error page
        if(empty($position->id))
        {
            return abort(404, 'Not Found');
        }

        $position->delete();

        return response()->json(['success' => 'Record has been deleted'], 200);
    }
}
