<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class EmployeeMasterDataMaintenance
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

        //Employee Master Data Record
        if($request->is('api/employee_master_data/index')){
            if($user->can('employee-master-data-list')){
                return $next($request); 
            }
        }

        //Employee Master Data Create
        if($request->is('api/employee_master_data/create') || $request->is('api/employee_master_data/store')){
            if($user->can('employee-master-data-create')){
                return $next($request); 
            }
        }

        //Employee Master Data Edit
        if($request->is('api/employee_master_data/edit/*') || $request->is('api/employee_master_data/update/*')){
            if($user->can('employee-master-data-edit')){
                return $next($request); 
            }
        }

        //Employee Master Data Delete
        if($request->is('api/employee_master_data/delete')){
            if($user->can('employee-master-data-delete')){
                return $next($request); 
            }
        }

        //Employee Master Data Import Data
        if($request->is('api/employee_master_data/import')){
            if($user->can('employee-master-data-import')){
                return $next($request); 
            }
        }

        //Employee Master Data Export Data
        if($request->is('api/employee_master_data/export')){
            if($user->can('employee-master-data-export')){
                return $next($request); 
            }
        }

        //Employee Master Data Delete All
        if($request->is('api/employee_master_data/delete')){
            if($user->can('employee-master-data-clear-list')){
                return $next($request); 
            }
        }
        

        return abort(401, 'Unauthorized');
    }
}
