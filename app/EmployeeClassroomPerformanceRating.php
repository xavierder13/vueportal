<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EmployeeClassroomPerformanceRating extends Model
{
    protected $fillable = ['employee_id', 'department', 'grade'];
}
