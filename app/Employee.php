<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'branch_id',
        'employee_code',
        'last_name',
        'first_name',
        'middle_name',
        'dob',
        'address',
        'contact',
        'email',
        'class',
        'rank',
        'department',
        'cost_center_code',
        'job_description',
        'date_employed',
        'gender',
        'civil_status',
        'tax_status',
        'tin_no',
        'tax_branch_code',
        'pagibig_no',
        'philhealth_no',
        'sss_no',
        'time_schedule',
        'restday',
        'educ_attain',
        'school_attended',
        'course'
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }


}
