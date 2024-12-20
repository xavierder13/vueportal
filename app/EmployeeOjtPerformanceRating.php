<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeOjtPerformanceRating extends Model
{
    protected $fillable = ['employee_id', 'mentor', 'grade', 'kpi'];
}
