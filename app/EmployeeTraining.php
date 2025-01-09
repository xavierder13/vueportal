<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeTraining extends Model
{
    protected $fillable = ['employee_id', 'mentor', 'grade', 'kpi', 'remarks'];
}
