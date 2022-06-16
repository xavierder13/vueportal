<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Branch;
use App\MarketingEvent;
use App\TacticalRequisition;
use App\TacticalRequisitionRow;
use App\TacticalRequisitionSubRow;
use App\TacticalRequisitionAttachment;
use App\AccessChart;
use App\AccessChartUserMap;
use Validator;
use DB;

class TacticalRequisitionController extends Controller
{   

    public function index() 
    {
        $tactical_requisitions = TacticalRequisition::with('branch')
                                                    ->with('marketing_event')
                                                    ->selectRaw("*, DATE_FORMAT(created_at, '%m/%d/%Y') as date_created")
                                                    ->get();
        
        // select max access level of Tactical Requisition Module
        $max_approver_level = AccessChart::join('access_chart_user_maps', 'access_charts.id', '=', 'access_chart_user_maps.access_chart_id')
                                         ->where('access_charts.access_for', '=', 1)
                                         ->max('access_chart_user_maps.access_level');

        return response()->json(['tactical_requisitions' => $tactical_requisitions, 'max_approver_level' => $max_approver_level], 200);
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
            $tactical_row->line_num = $key;
            $tactical_row->description = $value['description'];
            $tactical_row->resource_person = $value['resource_person'];
            $tactical_row->contact = $value['contact'];
            $tactical_row->qty = $value['qty'];
            $tactical_row->unit_cost = $value['unit_cost'];
            $tactical_row->amount = $value['amount'];
            $tactical_row->save();
            
            $sub_rows = $value['expense_sub_particulars'];
            
            // if has sub items
            if(count($sub_rows))
            {   
                foreach ($sub_rows as $i => $val) {
                    $tactical_sub_row = new TacticalRequisitionSubRow();
                    $tactical_sub_row->tactical_requisition_row_id = $tactical_row->id;
                    $tactical_sub_row->line_num = $i;
                    $tactical_sub_row->description = $val['description'];
                    $tactical_sub_row->resource_person = $val['resource_person'];
                    $tactical_sub_row->contact = $val['contact'];
                    $tactical_sub_row->qty = $val['qty'];
                    $tactical_sub_row->unit_cost = $val['unit_cost'];
                    $tactical_sub_row->amount = $val['amount'];
                    $tactical_sub_row->save();
                } 
            }
        }

        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                         ->with('tactical_rows.tactical_sub_rows')
                                         ->with('tactical_attachments')
                                         ->where('id', '=', $tactical_requisition_id)
                                         ->first();

        return response()->json(['success' => 'Record has successfully added', 'tactical_requisition' => $tactical_requisition]);
    }

    public function edit(Request $request)
    {
        $branches = Branch::all();
        $marketing_events = MarketingEvent::with('expense_particulars')
                                          ->with('expense_particulars.expense_sub_particulars')
                                          ->get();

        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                                    ->with('tactical_rows.tactical_sub_rows')
                                                    ->with('tactical_attachments')
                                                    ->where('id', '=', $request->get('tactical_requisition_id'))
                                                    ->first();

        return response()->json([
            'tactical_requisitions' => $tactical_requisition, 
            'branches' => $branches, 
            'marketing_events' => $marketing_events
        ], 200);
    }

    public function update(Request $request, $tactical_requisition_id)
    {   
        // return $request;
        $rules = [
            // 'branch_id.required' => 'Branch ID is required',
            // 'branch_id.integer' => 'Branch ID must be an integer',
            // 'marketing_event_id.required' => 'Marketing Event ID is required',
            // 'marketing_event_id.integer' => 'Marketing Event ID must be an integer',
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
            // 'branch_id' => 'required|integer',
            // 'marketing_event_id' => 'required|integer',
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
        
        $tactical_requisition = TacticalRequisition::find($tactical_requisition_id);
        // $tactical_requisition->branch_id = $request->get('branch_id');
        // $tactical_requisition->marketing_event_id = $request->get('marketing_event_id');
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
            $tactical_row = TacticalRequisitionRow::find($value['tactical_requisition_row_id']);
            $tactical_row->description = $value['description'];
            $tactical_row->resource_person = $value['resource_person'];
            $tactical_row->contact = $value['contact'];
            $tactical_row->qty = $value['qty'];
            $tactical_row->unit_cost = $value['unit_cost'];
            $tactical_row->amount = $value['amount'];
            $tactical_row->save();
            
            $sub_rows = $value['expense_sub_particulars'];
            
            // if has sub items
            if(count($sub_rows))
            {   
                foreach ($sub_rows as $i => $val) {
                    $tactical_sub_row = TacticalRequisitionSubRow::find($val['tactical_requisition_sub_row_id']);
                    $tactical_sub_row->description = $val['description'];
                    $tactical_sub_row->resource_person = $val['resource_person'];
                    $tactical_sub_row->contact = $val['contact'];
                    $tactical_sub_row->qty = $val['qty'];
                    $tactical_sub_row->unit_cost = $val['unit_cost'];
                    $tactical_sub_row->amount = $val['amount'];
                    $tactical_sub_row->save();
                } 
            }
        }

        $tactical_requisition = TacticalRequisition::with('tactical_rows')
                                         ->with('tactical_rows.tactical_sub_rows')
                                         ->with('tactical_attachments')
                                         ->where('id', '=', $tactical_requisition_id)
                                         ->first();

        return response()->json(['success' => 'Record has successfully added', 'tactical_requisition' => $tactical_requisition]);
    }
}
