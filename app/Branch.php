<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{

    protected $fillable = ['code', 'name'];

    public function employees()
    {
        return $this->hasMany('App\Employee', 'branch_id', 'id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function company()
    {
        return $this->hasOne('App\Company', 'id', 'company_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

}
