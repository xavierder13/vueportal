<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeBranchAssignmentPositionMaintenance
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

        //Employee Master Data Branch Assignment Position Record
        if($request->is('api/employee_master_data/branch_assignment_position/index')){
            if($user->can('employee-master-data-branch-assignment-position-list')){
                return $next($request); 
            }
        }

        //Employee Master Data Branch Assignment Position Create
        if($request->is('api/employee_master_data/branch_assignment_position/create') || $request->is('api/employee_master_data/branch_assignment_position/store')){
            if($user->can('employee-master-data-branch-assignment-position-create')){
                return $next($request); 
            }
        }

        //Employee Master Data Branch Assignment Position Edit
        if($request->is('api/employee_master_data/branch_assignment_position/edit/*') || $request->is('api/employee_master_data/branch_assignment_position/update/*')){
            if($user->can('employee-master-data-branch-assignment-position-edit')){
                return $next($request); 
            }
        }

        //Employee Master Data Branch Assignment Position Delete
        if($request->is('api/employee_master_data/branch_assignment_position/delete')){
            if($user->can('employee-master-data-branch-assignment-position-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
