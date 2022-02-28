<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ExpenseParticular extends Model
{
    protected $fillable = ['description', 'active'];

    // public function product()
    // {   
    //     return $this->belongsTo('App\Product', 'product_category_id','id');
    //     //                 ( <Model>, <id_of_specified_Model>, id_of_this_model>  )
    // }
}
