<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

// Authentication Route
Route::prefix('auth')->group(function(){
    Route::get('/init', [
        'uses' => 'API\AuthController@init',
        'as' => 'auth.init'
    ])->middleware('auth:api');

    Route::post('/login', [
        'uses' => 'API\AuthController@login',
        'as' => 'auth.login'
    ]);

    Route::post('/register', [
        'uses' => 'API/AuthController@register',
        'as' => 'auth.register'
    ]);

    Route::get('/logout', [
        'uses' => 'API\AuthController@logout',
        'as' => 'auth.logout'
    ])->middleware('auth:api');
});

// User Routes
Route::group(['prefix' => 'user', 'middleware' => ['auth:api', 'user.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\UserController@index',
        'as' => 'user.index',
    ]);

    Route::get('/create', [
        'uses' => 'API\UserController@create',
        'as' => 'user.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\UserController@store',
        'as' => 'user.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\UserController@edit',
        'as' => 'user.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\UserController@update',
        'as' => 'user.update',
    ]);

    Route::post('/delete', [
        'uses' => 'API\UserController@delete',
        'as' => 'user.delete',
    ]);

    Route::get('/roles_permissions', [
        'uses' => 'API\UserController@userRolesPermissions',
        'as' => 'user.roles_permissions',
    ]);

});

// Product Routes
Route::group(['prefix' => 'product', 'middleware' => ['auth:api', 'product.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\ProductController@index',
        'as' => 'product.index',
    ]);

    Route::get('/create', [
        'uses' => 'API\ProductController@create',
        'as' => 'product.create',
    ]);

    Route::post('/store', [
        'uses' => 'API\ProductController@store',
        'as' => 'product.store',
    ]);

    Route::get('/edit/{id}', [
        'uses' => 'API\ProductController@edit',
        'as' => 'product.edit',
    ]);

    Route::post('/update/{id}', [
        'uses' => 'API\ProductController@update',
        'as' => 'product.update',
    ]);

    Route::post('/delete', [
        'uses' => 'API\ProductController@delete',
        'as' => 'product.delete',
    ]);

    Route::get('/export', [
        'uses' => 'API\ProductController@export',
        'as' => 'product.export',
    ]);

});

Route::get('/product/export/{id}', [
    'uses' => 'API\ProductController@export',
    'as' => 'product.export',
])->middleware('product.maintenance');

//Permissions
Route::group(['prefix' => 'permission', 'middleware' => ['auth:api', 'permission.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\PermissionController@index',
        'as' => 'permission.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\PermissionController@create',
        'as' => 'permission.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\PermissionController@store',
        'as' => 'permission.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\PermissionController@edit',
        'as' => 'permission.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\PermissionController@update',
        'as' => 'permission.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\PermissionController@delete',
        'as' => 'permission.delete',
    ]);

});

//Roles
Route::group(['prefix' => 'role', 'middleware' => ['auth:api', 'role.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\RoleController@index',
        'as' => 'role.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\RoleController@create',
        'as' => 'role.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\RoleController@store',
        'as' => 'role.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\RoleController@edit',
        'as' => 'role.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\RoleController@update',
        'as' => 'role.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\RoleController@delete',
        'as' => 'role.delete',
    ]);

});

// Brand Routes
Route::group(['prefix' => 'brand', 'middleware' => ['auth:api', 'brand.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\BrandController@index',
        'as' => 'brand.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\BrandController@create',
        'as' => 'brand.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\BrandController@store',
        'as' => 'brand.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\BrandController@edit',
        'as' => 'brand.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\BrandController@update',
        'as' => 'brand.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\BrandController@delete',
        'as' => 'brand.delete',
    ]);

});

// Branch Routes
Route::group(['prefix' => 'branch', 'middleware' => ['auth:api', 'branch.maintenance']], function(){
    Route::get('/index', [
        'uses' => 'API\BranchController@index',
        'as' => 'branch.index',
    ]);
    Route::get('/create', [
        'uses' => 'API\BranchController@create',
        'as' => 'branch.create',
    ]);
    Route::post('/store', [
        'uses' => 'API\BranchController@store',
        'as' => 'branch.store',
    ]);
    Route::post('/edit', [
        'uses' => 'API\BranchController@edit',
        'as' => 'branch.edit',
    ]);
    Route::post('/update/{id}', [
        'uses' => 'API\BranchController@update',
        'as' => 'branch.update',
    ]);
    Route::post('/delete', [
        'uses' => 'API\BranchController@delete',
        'as' => 'branch.delete',
    ]);

});

//Activity Logs
Route::group(['prefix' => 'activity_logs', 'middleware' => ['auth:api', 'activity.logs']], function(){
    Route::get('/index', [
        'uses' => 'API\ActivityLogController@activity_logs',
        'as' => 'activity_logs.index',
    ]);
    
});
