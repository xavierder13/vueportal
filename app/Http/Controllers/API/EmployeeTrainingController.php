<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeTraining;
use Validator;

class EmployeeTrainingController extends Controller
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

        $training = new EmployeeTraining();
        $training->employee_id = $request->employee_id;
        $training->mentor = $request->mentor;
        $training->grade = $request->grade;
        $training->kpi = $request->kpi;
        $training->remarks = $request->remarks;
        $training->save();

        $trainings = EmployeeTraining::where('employee_id', $request->employee_id)
                                                            ->orderBy('id')                                                            ->get();

        return response()->json(['success' => 'Record has been added', 'trainings' => $trainings], 200);
    }

    public function update(Request $request, $id)
    {       

        $valid_fields = [
            'mentor' => 'required',
            'grade' => 'numeric|between:0, 999999.99',
            'kpi' => 'numeric|between:0, 999999.99'
        ];

        $rules = [
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

        $training = EmployeeTraining::find($id);
        $training->mentor = $request->mentor;
        $training->grade = $request->grade;
        $training->kpi = $request->kpi;
        $training->remarks = $request->remarks;
        $training->save();

        $trainings = EmployeeTraining::where('employee_id', $training->employee_id)
                                                            ->get();

        return response()->json(['success' => 'Record has been updated', 'trainings' => $trainings], 200);
    }
    
    public function delete(Request $request)
    {
        $training_id = $request->training_id;
        $training = EmployeeTraining::findOrFail($training_id);
        
        $training->delete();

        $trainings = EmployeeTraining::where('employee_id', $request->employee_id)
                                                           ->get();
        
        return response()->json(['success' => 'Record has been deleted', 'trainings' => $trainings], 200);
    }
}
