<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Branch;
use App\MarketingEvent;
use App\TacticalRequisition;
use App\TacticalRequisitionRow;
use App\TacticalRequisitionAttachment;
use Validator;
use DB;

class TacticalRequisitionController extends Controller
{   

    public function index()
    {
        $tactical_requisitions = DB::table('tactical_requisitions')
                                   ->leftJoin('marketing_events', 'tactical_requisitions.marketing_event_id', '=', 'marketing_events.id')
                                   ->select('tactical_requisitions.id', 'tactical_requisitions.id')
                                   ->get();
        return response()->json(['tactical_requisitions' => $tactical_requisitions], 200);
    }

    public function create() 
    {
        $branches = Branch::all();
        $marketing_events = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->get();
        
        return response()->json(['branches' => $branches, 'marketing_events' => $marketing_events], 200);
    }

    public function store(Request $request)
    {   

        $rules = [
            'branch_id.required' => 'Branch ID is required',
            'branch_id.integer' => 'Branch ID must be an integer',
            'marketing_event_id.required' => 'Marketing Event ID is required',
            'marketing_event_id.integer' => 'Marketing Event ID must be an integer',
            'venue.required' => 'Venue is required',
            'sponsor.required' => 'Sponsor is required',
            'period_from.required' => 'Period Date From is required',
            'period_from.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'period_to.required' => 'Period Date From is required',
            'period_to.date_format' => 'Invalid date. Format: (YYYY-MM-DD)',
            'operating_from.required' => 'Operating Hour From is required',
            'operating_to.required' => 'Operating Hour To is required',
            'expense_particulars.required' => 'Expense Particulars is required',

        ];

        $validator = Validator::make($request->all(), [
            'branch_id' => 'required|integer',
            'marketing_event_id' => 'required|integer',
            'venue' => 'required',
            'sponsor' => 'required',
            'period_from' => 'required|date_format:Y-m-d',
            'period_to' => 'required|date_format:Y-m-d',
            'operating_from' => 'required',
            'operating_to' => 'required',
            'expense_particulars' => 'required',

        ], $rules);

        if($validator->fails())
        {
            return response()->json($validator->errors(), 200);
        }
        
        $tactical_requisition = new TacticalRequisition();
        $tactical_requisition->branch_id = $request->get('branch_id');
        $tactical_requisition->marketing_event_id = $request->get('marketing_event_id');
        $tactical_requisition->sponsor = $request->get('sponsor');
        $tactical_requisition->venue = $request->get('venue');
        $tactical_requisition->period_from = $request->get('period_from');
        $tactical_requisition->period_to = $request->get('period_to');
        $tactical_requisition->operating_to = $request->get('operating_to');
        $tactical_requisition->operating_from = $request->get('operating_from');
        $tactical_requisition->status = 'Pending';
        $tactical_requisition->date_approve = null;
        $tactical_requisition->save();

        $tactical_requisition_id = $tactical_requisition->id;

        $expense_particulars = $request->get('expense_particulars');

        foreach ($expense_particulars as $key => $value) {
            $tactical_row = new TacticalRequisitionRow();
            $tactical_row->tactical_requisition_id = $tactical_requisition_id;
            $tactical_row->description = $value['description'];
            $tactical_row->resource_person = $value['resource_person'];
            $tactical_row->contact = $value['contact'];
            $tactical_row->qty = $value['qty'];
            $tactical_row->unit_cost = $value['unit_cost'];
            $tactical_row->amount = $value['amount'];
            $tactical_row->save();
        }

        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                         ->with('tactical_attachments')
                                         ->where('id', '=', $tactical_requisition_id);
        

        return response()->json(['success' => 'Record has successfully added', 'tactical_requisition' => $tactical_requisition]);
    }
}
