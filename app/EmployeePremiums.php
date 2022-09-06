<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeePremiums extends Model
{
    protected $fillable = [
        'branch_id',
        'last_name',
        'first_name',
        'middle_name',
        'dob',
        'date_hired',
        'or_number',
        'sss_no',
        'sss_ee',
        'sss_er',
        'sss_ec',
        'sss_total',
        'philhealth_no',
        'philhealth_ee',
        'philhealth_er',
        'philhealth_total',
        'pagibig_no',
        'pagibig_ee',
        'pagibig_er',
        'pagibig_total',						
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
