<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Branch;
use App\MarketingEvent;

class TacticalRequisitionController extends Controller
{
    public function create() 
    {
        $branches = Branch::all();
        $marketing_events = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->get();
        
        return response()->json(['branches' => $branches, 'marketing_events' => $marketing_events], 200);
    }
}
