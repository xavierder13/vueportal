<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeTrainingMaintenance
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

        //Employee Master Data Training Record
        if($request->is('api/employee_master_data/training/index')){
            if($user->can('employee-master-data-training-list')){
                return $next($request); 
            }
        }

        //Employee Master Data Training Create
        if($request->is('api/employee_master_data/training/create') || $request->is('api/employee_master_data/training/store')){
            if($user->can('employee-master-data-training-create')){
                return $next($request); 
            }
        }

        //Employee Master Data Training Edit
        if($request->is('api/employee_master_data/training/edit/*') || $request->is('api/employee_master_data/training/update/*')){
            if($user->can('employee-master-data-training-edit')){
                return $next($request); 
            }
        }

        //Employee Master Data Training Delete
        if($request->is('api/employee_master_data/training/delete')){
            if($user->can('employee-master-data-training-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
