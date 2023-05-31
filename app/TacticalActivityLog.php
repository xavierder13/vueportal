<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TacticalActivityLog extends Model
{
    protected $fillable = [
        'document_id',
        'approver_id',
        'level',
        'status',
    ];

    public function approver()
    {
        return $this->hasOne('App\User', 'id', 'approver_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function tactical_requisition()
    {   
        return $this->belongsTo('App\TacticalRequisition', 'id','document_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model>  )
    }
}
