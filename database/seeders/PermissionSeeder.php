<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $modules = [
            'products',
            'bundles',
            'categories',
            'brands',
            'units',
            'suppliers',
            'purchases',
            'sales',
            'expenses',
            'expense_categories',
            'sliders',
            'menus',
            'pages',
            'customers',
            'users',
            'settings',
            'roles',
            'reports',
            'inventory',
            'returns',
        ];

        $actions = ['view', 'create', 'edit', 'delete'];

        $permissions = [];
        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions[] = $action . '_' . $module;
            }
        }

        // Add special permissions
        $permissions[] = 'access_pos';
        $permissions[] = 'manage_dashboard';

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Assign all permissions to Super Admin and Administrator
        $superAdmin = Role::where('name', 'Super Admin')->first();
        if ($superAdmin) {
            $superAdmin->syncPermissions(Permission::all());
        }

        $admin = Role::where('name', 'Administrator')->first();
        if ($admin) {
            $admin->syncPermissions(Permission::all());
        }

        // Default permission sets for the staff-facing roles.
        $defaultRolePermissions = [
            'Manager' => [
                'products', 'bundles', 'categories', 'brands', 'units', 'suppliers',
                'purchases', 'sales', 'expenses', 'expense_categories', 'sliders',
                'menus', 'pages', 'customers', 'reports', 'inventory', 'returns',
            ],
            'Seller' => [
                'products' => ['view', 'create', 'edit'],
                'bundles' => ['view', 'create', 'edit'],
                'sales' => ['view', 'create', 'edit'],
                'customers' => ['view', 'create', 'edit'],
                'returns' => ['view', 'create'],
            ],
            'Accounts' => [
                'sales' => ['view'],
                'purchases' => ['view'],
                'expenses' => ['view', 'create', 'edit'],
                'expense_categories' => ['view', 'create', 'edit'],
                'reports' => ['view'],
                'customers' => ['view'],
                'returns' => ['view'],
            ],
        ];

        foreach ($defaultRolePermissions as $roleName => $moduleConfig) {
            $role = Role::firstOrCreate(['name' => $roleName]);
            $rolePermissions = [];

            foreach ($moduleConfig as $key => $value) {
                if (is_int($key)) {
                    // Full view/create/edit/delete access to this module.
                    foreach ($actions as $action) {
                        $rolePermissions[] = $action . '_' . $value;
                    }
                } else {
                    foreach ($value as $action) {
                        $rolePermissions[] = $action . '_' . $key;
                    }
                }
            }

            $rolePermissions[] = 'manage_dashboard';

            $role->syncPermissions(array_values(array_intersect($rolePermissions, $permissions)));
        }
    }
}
