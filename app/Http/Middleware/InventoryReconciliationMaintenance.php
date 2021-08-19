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

        //Inventory Reconciliation Record
        if($request->is('api/inventory_reconciliation/index') || 
           $request->is('api/inventory_reconciliation/discrepancy/*') ||
           $request->is('api/inventory_reconciliation/breakdown/*')){
            if($user->can('inventory-recon-list')){
                return $next($request); 
            }
        }

        //Inventory Reconciliation Import
        if($request->is('api/inventory_reconciliation/import')){
            if($user->can('inventory-recon-create')){
                return $next($request); 
            }
        }

        //Product Reconcile for Branch user
        if($request->is('api/inventory_reconciliation/reconcile') || $request->is('api/inventory_reconciliation/unreconcile/list')){
            if($user->can('product-reconcile')){
                return $next($request); 
            }
        }

        //Inventory Reconciliation Delete
        if($request->is('api/inventory_reconciliation/delete')){
            if($user->can('inventory-recon-delete')){
                return $next($request); 
            }
        }

        return abort(401, 'Unauthorized');
    }
}
