<?php

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $permissions = [
            
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',
            'brand-list',
            'brand-create',
            'brand-edit',
            'brand-delete',
            'branch-list',
            'branch-create',
            'branch-edit',
            'branch-delete',
            'product-list',
            'product-create',
            'product-edit',
            'product-delete',
            'product-clear-list',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',
            'activity-logs',
            
         ];
 
 
         foreach ($permissions as $permission) {
              Permission::create(['name' => $permission]);
         }
    }
}
