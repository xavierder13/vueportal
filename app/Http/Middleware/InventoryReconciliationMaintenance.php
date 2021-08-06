<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class InventoryReconciliationMaintenance
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

        //Product Record
        if($request->is('api/inventory_reconciliation/index')){
            if($user->can('inventory-recon-list')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
