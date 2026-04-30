<?php

use Illuminate\Database\Migrations\Migration;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $modules = [
            'kpi',
            'kpi_role_config',
            'kpi_target',
            'kpi_template',
        ];

        $actions = ['access', 'create', 'edit', 'view', 'delete'];
        $permissions = collect();

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                $permissions->push(Permission::firstOrCreate([
                    'name' => "{$module}_{$action}",
                    'guard_name' => 'web',
                ]));
            }
        }

        $adminRoleName = config('access.users.admin_role');
        Role::whereIn('name', array_filter([$adminRoleName, 'teacher']))
            ->get()
            ->each(function (Role $role) use ($permissions) {
                $role->givePermissionTo($permissions);
            });

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function down()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        Permission::whereIn('name', [
            'kpi_access',
            'kpi_create',
            'kpi_edit',
            'kpi_view',
            'kpi_delete',
            'kpi_role_config_access',
            'kpi_role_config_create',
            'kpi_role_config_edit',
            'kpi_role_config_view',
            'kpi_role_config_delete',
            'kpi_target_access',
            'kpi_target_create',
            'kpi_target_edit',
            'kpi_target_view',
            'kpi_target_delete',
            'kpi_template_access',
            'kpi_template_create',
            'kpi_template_edit',
            'kpi_template_view',
            'kpi_template_delete',
        ])->delete();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
};
