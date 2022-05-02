<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalRequisitionRow extends Model
{
    protected $fillable = [
        'tactical_requisition_id',
        'description', 
        'resource_person',
        'contact',
        'qty',
        'unit_cost',
        'amount',
    ];

    public function tactical_requisition()
    {   
        return $this->belongsTo('App\TacticalRequisition', 'id','tactical_requisition_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model>  )
    }
}
