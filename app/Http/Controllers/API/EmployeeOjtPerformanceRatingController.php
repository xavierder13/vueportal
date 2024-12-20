<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeOjtPerformanceRating;
use Validator;

class EmployeeOjtPerformanceRatingController extends Controller
{
    public function store(Request $request)
    {       

        $valid_fields = [
            'employee_id' => 'required|integer',
            'mentor' => 'required',
            'grade' => 'numeric|between:0, 999999.99',
            'kpi' => 'numeric|between:0, 999999.99'
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'mentor.required' => 'Mentor is required',
            'grade.numeric' => 'Invalid value',
            'grade.between' => 'Invalid value',
            'kpi.numeric' => 'Invalid value',
            'kpi.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $performance = new EmployeeOjtPerformanceRating();
        $performance->employee_id = $request->employee_id;
        $performance->mentor = $request->mentor;
        $performance->grade = $request->grade;
        $performance->kpi = $request->kpi;
        $performance->save();

        $performances = EmployeeOjtPerformanceRating::where('employee_id', $request->employee_id)
                                                            ->orderBy('id')                                                            ->get();

        return response()->json(['success' => 'Record has been added', 'performances' => $performances], 200);
    }

    public function update(Request $request, $id)
    {       

        $valid_fields = [
            'employee_id' => 'required|integer',
            'mentor' => 'required',
            'grade' => 'numeric|between:0, 999999.99',
            'kpi' => 'numeric|between:0, 999999.99'
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'mentor.required' => 'Mentor is required',
            'grade.numeric' => 'Invalid value',
            'grade.between' => 'Invalid value',
            'kpi.numeric' => 'Invalid value',
            'kpi.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $performance = EmployeeOjtPerformanceRating::find($id);
        $performance->mentor = $request->mentor;
        $performance->grade = $request->grade;
        $performance->kpi = $request->kpi;
        $performance->save();

        $performances = EmployeeOjtPerformanceRating::where('employee_id', $performance->employee_id)
                                                            ->get();

        return response()->json(['success' => 'Record has been updated', 'performances' => $performances], 200);
    }
    
    public function delete(Request $request)
    {
        $performance_id = $request->performance_id;
        $performance = EmployeeOjtPerformanceRating::findOrFail($performance_id);
        
        $performance->delete();

        $performances = EmployeeOjtPerformanceRating::where('employee_id', $request->employee_id)
                                                           ->get();
        
        return response()->json(['success' => 'Record has been deleted', 'performances' => $performances], 200);
    }
}
