<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MarketingEvent extends Model
{
    protected $fillable = ['event_name', 'active'];

    public function expense_particulars()
    {
        return $this->hasMany('App\ExpenseParticular', 'marketing_event_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
