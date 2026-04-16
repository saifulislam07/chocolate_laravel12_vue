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
            'settings',
            'roles',
            'reports'
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
    }
}
