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
            'product-export',
            'product-clear-list',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'employee-list',
            'employee-list-import',
            'employee-list-export',
            'employee-clear-list',
            'employee-resigned-list',
            'employee-resigned-import',
            'employee-resigned-export',
            'employee-resigned-clear-list',
            'employee-payroll-list',
            'employee-payroll-import',
            'employee-payroll-export',
            'employee-payroll-clear-list',
            'employee-absences-list',
            'employee-absences-import',
            'employee-absences-export',
            'employee-absences-clear-list',
            'employee-overtime-list',
            'employee-overtime-import',
            'employee-overtime-export',
            'employee-overtime-clear-list',
            'employee-holiday-pay-list',
            'employee-holiday-pay-import',
            'employee-holiday-pay-export',
            'employee-holiday-pay-clear-list',
            'employee-loans-list',
            'employee-loans-import',
            'employee-loans-export',
            'employee-loans-clear-list',
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
