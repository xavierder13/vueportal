<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\EmployeeMasterData;
use App\EmployeeBranchAssignmentPosition;
use App\Branch;
use App\Position;
use Validator;
use Carbon\Carbon;

class EmployeeBranchAssignmentPositionController extends Controller
{
    public function store(Request $request)
    {       
        $employee_id = $request->employee_id;

        $valid_fields = [
            'employee_id' => 'required|integer',
            'date_assigned' => 'required|date_format:Y-m-d',
            'position' => 'required',
            'branch' => 'required',
            'remarks' => 'required',
        ];

        $rules = [
            'employee_id.required' => 'Employee ID is required',
            'date_assigned.date_format' => 'Date Assigned is required',
            'date_assigned.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'position.required' => 'Position is required',
            'branch.required' => 'Branch is required',
            'remarks.required' => 'Remarks is required',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $branch_assignment = new EmployeeBranchAssignmentPosition();
        $branch_assignment->employee_id = $employee_id;
        $branch_assignment->date_assigned =  $request->date_assigned;
        $branch_assignment->position = $request->position;
        $branch_assignment->branch = $request->branch;
        $branch_assignment->remarks = $request->remarks;
        $branch_assignment->save();

        $branch_assignment_positions = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                        ->orderBy('date_assigned')                                                            
                                                        ->get();

        // get all in descending order then get first data to get the latest based on assiged date 
        $max_branch_assigment = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                                ->orderBy('date_assigned', 'DESC')                                                            
                                                                ->get()
                                                                ->first()
                                                                ->branch;

        $max_position_assigment = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                                ->orderBy('date_assigned', 'DESC')                                                            
                                                                ->get()
                                                                ->first()
                                                                ->position;

        $branch_id = Branch::where('name', $max_branch_assigment)->get()->first()->id;

        $position_id = Position::where('name', $max_position_assigment)->get()->first()->id;

         // update employee master data branch and position based from branch assignment and positions table
        EmployeeMasterData::where('id', $employee_id)
                            ->update(['branch_id' => $branch_id, 'position_id' => $position_id]);
        
        
        return response()->json(['success' => 'Record has been added', 'branch_assignment_positions' => $branch_assignment_positions], 200);
    }

    public function update(Request $request, $id)
    {       
        $employee_id = $request->employee_id;

        $valid_fields = [
            'date_assigned' => 'required|date_format:Y-m-d',
            'position' => 'required',
            'branch' => 'required',
            'remarks' => 'required',
        ];

        $rules = [
            'date_assigned.date_format' => 'Date Assigned is required',
            'date_assigned.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'position.required' => 'Position is required',
            'branch.required' => 'Branch is required',
            'remarks.required' => 'Remarks is required',
        ];

        $validator = Validator::make($request->all(), $valid_fields, $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors());
        }

        $branch_assignment = EmployeeBranchAssignmentPosition::find($id);
        $branch_assignment->date_assigned =  $request->date_assigned;
        $branch_assignment->position = $request->position;
        $branch_assignment->branch = $request->branch;
        $branch_assignment->remarks = $request->remarks;
        $branch_assignment->save();

        $branch_assignment_positions = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                        ->orderBy('date_assigned')                                                            
                                                        ->get();

        // get all in descending order then get first data to get the latest based on assiged date 
        $max_branch_assigment = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                                ->orderBy('date_assigned', 'DESC')                                                            
                                                                ->get()
                                                                ->first()
                                                                ->branch;

        $branch_id = Branch::where('name', $max_branch_assigment)->get()->first()->id;

        // update employee master data branch and position based from branch assignment and positions table
        EmployeeMasterData::where('id', $employee_id)
                          ->update(['branch_id' => $branch_id]);

        return response()->json(['success' => 'Record has been updated', 'branch_assignment_positions' => $branch_assignment_positions], 200);
    }
    
    public function delete(Request $request)
    {
        $branch_assignment_id = $request->branch_assignment_id;
        $branch_assignment = EmployeeBranchAssignmentPosition::findOrFail($branch_assignment_id);
        
        $employee_id = $branch_assignment->employee_id;

        $branch_assignment->delete();

        $branch_assignment_positions = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                        ->orderBy('date_assigned')                                                            
                                                        ->get();

        // update employee master data branch and position based from branch assignment and positions table
        if(count($branch_assignment_positions))
        {
            // get all in descending order then get first data to get the latest based on assiged date 
            $max_branch_assigment = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                                    ->orderBy('date_assigned', 'DESC')                                                            
                                                                    ->get()
                                                                    ->first()
                                                                    ->branch;

            $max_position_assigment = EmployeeBranchAssignmentPosition::where('employee_id', $employee_id)
                                                                    ->orderBy('date_assigned', 'DESC')                                                            
                                                                    ->get()
                                                                    ->first()
                                                                    ->position;

            $branch_id = Branch::where('name', $max_branch_assigment)->get()->first()->id;

            $position_id = Position::where('name', $max_position_assigment)->get()->first()->id;

            EmployeeMasterData::where('id', $employee_id)
                              ->update(['branch_id' => $branch_id, 'position_id' => $position_id]);
        }
        
        return response()->json(['success' => 'Record has been deleted', 'branch_assignment_positions' => $branch_assignment_positions], 200);
    }
}
