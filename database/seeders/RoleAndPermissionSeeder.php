<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // PLU Management
            'view_plu_codes',
            'create_plu_codes',
            'update_plu_codes',
            'delete_plu_codes',
            'import_plu_codes',
            'export_plu_codes',
            'manage_plu_images',

            // User Management
            'view_users',
            'create_users',
            'update_users',
            'delete_users',
            'assign_roles',
            'view_user_activity',

            // List Management
            'view_all_lists',
            'update_all_lists',
            'delete_all_lists',
            'moderate_marketplace',
            'feature_lists',

            // Marketplace
            'view_marketplace_analytics',
            'manage_marketplace_categories',
            'approve_marketplace_listings',
            'remove_marketplace_listings',

            // System
            'view_system_settings',
            'update_system_settings',
            'view_analytics',
            'export_analytics',
            'manage_backups',
            'view_logs',
            'clear_cache',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions

        // Admin role - has all permissions
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Manager role - has most permissions except system critical ones
        $managerRole = Role::create(['name' => 'manager']);
        $managerRole->givePermissionTo([
            'view_plu_codes',
            'create_plu_codes',
            'update_plu_codes',
            'delete_plu_codes',
            'import_plu_codes',
            'export_plu_codes',
            'manage_plu_images',
            'view_users',
            'view_user_activity',
            'view_all_lists',
            'update_all_lists',
            'delete_all_lists',
            'moderate_marketplace',
            'feature_lists',
            'view_marketplace_analytics',
            'manage_marketplace_categories',
            'approve_marketplace_listings',
            'remove_marketplace_listings',
            'view_analytics',
            'export_analytics',
        ]);

        // User role - basic permissions
        $userRole = Role::create(['name' => 'user']);
        $userRole->givePermissionTo([
            'view_plu_codes',
        ]);
    }
}
