<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeClassroomPerformanceRatingMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $user = Auth::user();

        //Employee Master Data Classroom Performance Rating Record
        if($request->is('api/employee_master_data/classroom_performance_rating/index')){
            if($user->can('employee-master-data-classroom-performance-rating-list')){
                return $next($request); 
            }
        }

        //Employee Master Data Classroom Performance Rating Create
        if($request->is('api/employee_master_data/classroom_performance_rating/create') || $request->is('api/employee_master_data/classroom_performance_rating/store')){
            if($user->can('employee-master-data-classroom-performance-rating-create')){
                return $next($request); 
            }
        }

        //Employee Master Data Classroom Performance Rating Edit
        if($request->is('api/employee_master_data/classroom_performance_rating/edit/*') || $request->is('api/employee_master_data/classroom_performance_rating/update/*')){
            if($user->can('employee-master-data-classroom-performance-rating-edit')){
                return $next($request); 
            }
        }

        //Employee Master Data Classroom Performance Rating Delete
        if($request->is('api/employee_master_data/classroom_performance_rating/delete')){
            if($user->can('employee-master-data-classroom-performance-rating-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
