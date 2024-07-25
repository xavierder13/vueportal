<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Position;
use App\Rank;
use Validator;
use DB;

class PositionController extends Controller
{
    public function index()
    {       
        $positions = Position::with('rank')->get();
        $ranks = Rank::all();
        return response()->json(['positions' => $positions, 'ranks' => $ranks], 200);
    }

    public function create()
    {
        
    }


    public function store(Request $request)
    {   
        
        $rules = [
            'name.required' => 'Please enter position',
            'name.unique' => 'Position already exists',
            'rank_id.required' => 'Rank is required',
            'rank.integer' => 'Rank must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name',
            'rank_id' => 'required|integer',
        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }

        $position = new Position();
        $position->name = $request->get('name');
        $position->rank_id = $request->get('rank_id');
        $position->save();

        $position = Position::with('rank')->find($position->id);

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
            'rank_id.required' => 'Rank is required',
            'rank.integer' => 'Rank must be an integer',
        ];

        $validator = Validator::make($request->all(),[
            'name' => 'required|unique:positions,name,'.$position_id,
            'rank_id' => 'required|integer',
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
        $position->rank_id = $request->get('rank_id');
        $position->save();

        $position = Position::with('rank')->find($position->id);

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
