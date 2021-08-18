<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = ['name'];

    public function product()
    {   
        return $this->belongsTo('App\User', 'position_id','id');
        //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    }
}
