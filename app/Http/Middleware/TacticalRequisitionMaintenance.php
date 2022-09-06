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

        //Tactical Requisition Record
        if($request->is('api/tactical_requisition/index')){
            if($user->can('tactical-requisition-list')){
                return $next($request); 
            }
        }

        //Tactical Requisition Create
        if($request->is('api/tactical_requisition/create') || $request->is('api/tactical_requisition/store')){
            if($user->can('tactical-requisition-create')){
                return $next($request); 
            }
        }

        //Tactical Requisition Edit
        if($request->is('api/tactical_requisition/edit') || $request->is('api/tactical_requisition/update/*')){
            if($user->can('tactical-requisition-edit') || $user->can('tactical-requisition-approve')){
                return $next($request); 
            }
        }

        //Tactical Requisition Approve
        if($request->is($request->is('api/tactical_requisition/approve/*'))){
            if($user->can('tactical-requisition-approve')){
                return $next($request); 
            }
        }

        //Tactical Requisition Delete
        if($request->is('api/tactical_requisition/delete')){
            if($user->can('tactical-requisition-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
