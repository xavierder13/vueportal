<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Employee;
use App\Branch;
use Carbon\Carbon;
use Validator;
use DB;
use Excel;

class EmployeeController extends Controller
{
    public function index()
    {
        $employees = Employee::with('branch')->get();
        $branches = Branch::all();

        return response()->json(['employees' => $employees, 'branches' => $branches], 200);
    }
}
