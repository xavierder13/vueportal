<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeMeritHistory extends Model
{
    protected $fillable = ['employee_id', 'merit_date', 'salary'];
}
