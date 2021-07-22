<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{   

    protected $fillable = ['name', 'active'];

    public function product()
    {   
        return $this->belongsTo('App\Product', 'id','id');
        //                 ( <Model>, <id_of_this_model>, <id_of_specified_Model> )
    }
}
