<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeBranchAssignmentPosition extends Model
{
    protected $fillable = ['employee_id', 'date_assigned', 'position', 'branch', 'remarks'];
}
