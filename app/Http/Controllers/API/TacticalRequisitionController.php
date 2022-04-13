<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Branch;

class TacticalRequisitionController extends Controller
{
    public function create() 
    {
        $branches = Branch::all();

        return response()->json(['branches' => $branches], 200);
    }
}
