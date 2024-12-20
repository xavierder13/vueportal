<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeClassroomPerformanceRating;
use Validator;

class EmployeeClassroomPerformanceRatingController extends Controller
{
    public function store(Request $request)
    {       

        $valid_fields = [
            'employee_id' => 'required|integer',
            'department' => 'required',
            'grade' => 'numeric|between:0, 999999.99'
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'department.required' => 'Mentor is required',
            'grade.numeric' => 'Invalid value',
            'grade.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $performance = new EmployeeClassroomPerformanceRating();
        $performance->employee_id = $request->employee_id;
        $performance->department = $request->department;
        $performance->grade = $request->grade;
        $performance->save();

        $performances = EmployeeClassroomPerformanceRating::where('employee_id', $request->employee_id)
                                                            ->orderBy('department')
                                                            ->orderBy('id')                                                            ->get();

        return response()->json(['success' => 'Record has been added', 'performances' => $performances], 200);
    }

    public function update(Request $request, $id)
    {       

        $valid_fields = [
            'department' => 'required',
            'grade' => 'numeric|between:0, 999999.99'
        ];

        $rules = [
            'department.required' => 'Mentor is required',
            'grade.numeric' => 'Invalid value',
            'grade.between' => 'Invalid value',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $performance = EmployeeClassroomPerformanceRating::find($id);
        $performance->department = $request->department;
        $performance->grade = $request->grade;
        $performance->save();

        $performances = EmployeeClassroomPerformanceRating::where('employee_id', $performance->employee_id)
                                                            ->orderBy('department')
                                                            ->get();

        return response()->json(['success' => 'Record has been updated', 'performances' => $performances], 200);
    }
    
    public function delete(Request $request)
    {
        $performance_id = $request->performance_id;
        $performance = EmployeeClassroomPerformanceRating::findOrFail($performance_id);
        
        $performance->delete();

        $performances = EmployeeClassroomPerformanceRating::where('employee_id', $request->employee_id)
                                                            ->orderBy('department')
                                                            ->get();
        
        return response()->json(['success' => 'Record has been deleted', 'performances' => $performances], 200);
    }
}
