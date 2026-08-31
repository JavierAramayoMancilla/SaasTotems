<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'displays.view',
            'displays.create',
            'displays.update',
            'displays.delete',

            'advertisements.view',
            'advertisements.create',
            'advertisements.update',
            'advertisements.delete',

            'menus.view',
            'menus.create',
            'menus.update',
            'menus.delete',

            'menu_items.view',
            'menu_items.create',
            'menu_items.update',
            'menu_items.delete',

            'ad_schedules.view',
            'ad_schedules.create',
            'ad_schedules.update',
            'ad_schedules.delete',

            'analytics.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        $superAdmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => 'web',
        ]);

        $tenantAdmin = Role::firstOrCreate([
            'name' => 'tenant_admin',
            'guard_name' => 'web',
        ]);

        $user = Role::firstOrCreate([
            'name' => 'user',
            'guard_name' => 'web',
        ]);

        $superAdmin->syncPermissions($permissions);

        $tenantAdmin->syncPermissions([
            'users.view',
            'users.create',
            'users.update',
            'users.delete',

            'displays.view',
            'displays.create',
            'displays.update',

            'advertisements.view',
            'advertisements.create',
            'advertisements.update',

            'menus.view',
            'menus.create',
            'menus.update',

            'menu_items.view',
            'menu_items.create',
            'menu_items.update',

            'ad_schedules.view',
            'ad_schedules.create',
            'ad_schedules.update',

            'analytics.view',
        ]);

        $user->syncPermissions([
            'displays.view',

            'advertisements.view',
            'advertisements.create',
            'advertisements.update',

            'menus.view',
            'menus.create',
            'menus.update',

            'menu_items.view',
            'menu_items.create',
            'menu_items.update',

            'ad_schedules.view',
            'ad_schedules.create',
            'ad_schedules.update',

            'analytics.view',
        ]);
    }
}