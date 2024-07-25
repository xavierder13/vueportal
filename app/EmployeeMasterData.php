<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeMasterData extends Model
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
        'position_id',
        'department_id',
        'date_employed',
        'date_resigned',
        'gender',
        'civil_status',
        'tin_no',
        'pagibig_no',
        'philhealth_no',
        'sss_no',
        'educ_attain',
        'school_attended',
        'course',
        'active',
    ];

    public function branch()
    {
        return $this->hasOne('App\Branch', 'id', 'branch_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function position()
    {
        return $this->hasOne('App\Position', 'id', 'position_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }

    public function department()
    {
        return $this->hasOne('App\Department', 'id', 'department_id');
        //                 ( <Model>, <id_of_specified_Model>, <id_of_this_model> )
    }
}
