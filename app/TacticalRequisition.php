<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalRequisition extends Model
{
    protected $fillable = [
        'branch_id', 
        'marketing_event_id',
        'venue',
        'sponsor',
        'period_from',
        'period_to',
        'operating_from',
        'operating_to',
    ];

    public function tactical_rows()
    {   
        return $this->hasMany('App\TacticalRequisitionRow', 'tactical_requisition_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
    public function tactical_attachments()
    {   
        return $this->hasMany('App\TacticalRequisitionAttachment', 'tactical_requisition_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
