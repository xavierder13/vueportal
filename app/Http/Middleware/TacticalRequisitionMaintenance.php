<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class TacticalRequisitionMaintenance
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

        //Role Record
        if($request->is('api/tactical_requisition/index')){
            if($user->can('tactical-requisition-list')){
                return $next($request); 
            }
        }

        //Role Create
        if($request->is('api/tactical_requisition/create') || $request->is('api/tactical_requisition/store')){
            if($user->can('tactical-requisition-create')){
                return $next($request); 
            }
        }

        //Role Edit
        if($request->is('api/tactical_requisition/edit/*') || $request->is('api/tactical_requisition/update/*')){
            if($user->can('role-edit')){
                return $next($request); 
            }
        }

        //Role Delete
        if($request->is('api/tactical_requisition/delete')){
            if($user->can('role-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
