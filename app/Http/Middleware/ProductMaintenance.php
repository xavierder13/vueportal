<?php

namespace App\Http\Middleware;

use Closure;
use Auth;

class ProductMaintenance
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
        if($request->is('api/product/index')){
            if($user->can('product-list')){
                return $next($request); 
            }
        }

        //Product Create
        if($request->is('api/product/create') || 
           $request->is('api/product/store') || 
           $request->is('api/product/search_model') || 
           $request->is('api/product/search_serial')
        ){
            if($user->can('product-create')){
                return $next($request); 
            }
        }

        //Product Edit
        if($request->is('api/product/edit/*') || $request->is('api/product/update/*')){
            if($user->can('product-edit')){
                return $next($request); 
            }
        }

        //Product Delete
        if($request->is('api/product/delete')){
            if($user->can('product-delete')){
                return $next($request); 
            }
        }

        //Product Export
        if($request->is('api/product/export/*/*')){
            // if($user->can('product-export')){
            //     return $next($request); 
            // }
            return $next($request); 
        }

        // Product Serial Number Details
        if($request->is('api/product/serial_number_details')){
            if($user->can('serial-number-details')){
                return $next($request); 
            }
            return $next($request); 
        }

        // Sync Item Master Data
        if($request->is('api/product/sync_item_master_data')){
            if($user->can('sync-item-master-data')){
                return $next($request); 
            }
            return $next($request); 
        }


        return abort(401, 'Unauthorized');
    }
}
