<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeMeritHistory;
use Carbon\Carbon;
use Validator;

class EmployeeMeritHistoryController extends Controller
{
    public function store(Request $request)
    {       

        $valid_fields = [
            'employee_id' => 'required|integer',
            'merit_date' => 'required|date_format:Y-m-d',
            'salary' => 'nullable|numeric|between:0, 999999.99'
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'merit_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'salary.numeric' => 'Invalid value',
            'salary.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $history = new EmployeeMeritHistory();
        $history->employee_id = $request->employee_id;
        $history->merit_date = $request->merit_date;
        $history->salary = $request->salary;
        $history->save();

        $histories = EmployeeMeritHistory::where('employee_id', $request->employee_id)
                                             ->orderBy('merit_date')
                                             ->get();

        return response()->json(['success' => 'Record has been added', 'merit_histories' => $histories], 200);
    }

    public function update(Request $request, $id)
    {       

        $valid_fields = [
            'employee_id' => 'required|integer',
            'merit_date' => 'required|date_format:Y-m-d',
            'salary' => 'nullable|numeric|between:0, 999999.99'
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'merit_date.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'salary.numeric' => 'Invalid value',
            'salary.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $history = EmployeeMeritHistory::find($id);
        $history->employee_id = $request->employee_id;
        $history->merit_date = $request->merit_date;
        $history->salary = $request->salary;
        $history->save();

        $histories = EmployeeMeritHistory::where('employee_id', $request->employee_id)
                                             ->orderBy('merit_date')
                                             ->get();

        return response()->json(['success' => 'Record has been updated', 'merit_histories' => $histories], 200);
    }
    
    public function delete(Request $request)
    {
        $merit_history_id = $request->merit_history_id;
        $history = EmployeeMeritHistory::findOrFail($merit_history_id);
        
        $history->delete();

        $histories = EmployeeMeritHistory::where('employee_id', $request->employee_id)
                                        ->orderBy('merit_date')
                                        ->get();
        
        return response()->json(['success' => 'Record has been deleted', 'merit_histories' => $histories], 200);
    }
}
