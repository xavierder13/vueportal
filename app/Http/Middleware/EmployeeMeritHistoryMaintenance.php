<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeMeritHistoryMaintenance
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

        //Employee Master Data Merit History Record
        if($request->is('api/employee_master_data/merit_history/index')){
            if($user->can('employee-master-data-merit-history-list')){
                return $next($request); 
            }
        }

        //Employee Master Data Merit History Create
        if($request->is('api/employee_master_data/merit_history/create') || $request->is('api/employee_master_data/merit_history/store')){
            if($user->can('employee-master-data-merit-history-create')){
                return $next($request); 
            }
        }

        //Employee Master Data Merit History Edit
        if($request->is('api/employee_master_data/merit_history/edit/*') || $request->is('api/employee_master_data/merit_history/update/*')){
            if($user->can('employee-master-data-merit-history-edit')){
                return $next($request); 
            }
        }

        //Employee Master Data Merit History Delete
        if($request->is('api/employee_master_data/merit_history/delete')){
            if($user->can('employee-master-data-merit-history-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
