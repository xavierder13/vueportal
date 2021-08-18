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
            'position-list',
            'position-create',
            'position-edit',
            'position-delete',
            'product-list',
            'product-list-all',
            'product-create',
            'product-edit',
            'product-delete',
            'product-export',
            'product-clear-list',
            'product-reconcile',
            'product-category-list',
            'product-category-create',
            'product-category-edit',
            'product-category-delete',
            'inventory-recon-list',
            'inventory-recon-create',
            'inventory-recon-edit',
            'inventory-recon-delete',
            'inventory-recon-audit',
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',
            'employee-list',
            'employee-list-all',
            'employee-list-import',
            'employee-list-export',
            'employee-clear-list',
            'employee-resigned-list',
            'employee-resigned-all',
            'employee-resigned-import',
            'employee-resigned-export',
            'employee-resigned-clear-list',
            'employee-payroll-list',
            'employee-payroll-list-all',
            'employee-payroll-import',
            'employee-payroll-export',
            'employee-payroll-clear-list',
            'employee-absences-list',
            'employee-absences-list-all',
            'employee-absences-import',
            'employee-absences-export',
            'employee-absences-clear-list',
            'employee-overtime-list',
            'employee-overtime-list-all',
            'employee-overtime-import',
            'employee-overtime-export',
            'employee-overtime-clear-list',
            'employee-holiday-pay-list',
            'employee-holiday-pay-list-all',
            'employee-holiday-pay-import',
            'employee-holiday-pay-export',
            'employee-holiday-pay-clear-list',
            'employee-loans-list',
            'employee-loans-list-all',
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
