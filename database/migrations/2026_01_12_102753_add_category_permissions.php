<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration {
    public function up()
    {
        $actions = ['access', 'create', 'edit', 'view', 'delete'];
        $module  = 'category';

        foreach ($actions as $action) {
            Permission::firstOrCreate([
                'name' => "{$module}_{$action}",
                'guard_name' => 'web',
            ]);
        }

        // Assign permissions to Admin
        if ($admin = Role::where('name', config('access.users.admin_role'))->first()) {
            $admin->givePermissionTo(
                Permission::where('name', 'like', 'category_%')->get()
            );
        }
    }

    public function down()
    {
        Permission::where('name', 'like', 'category_%')->delete();
    }
};
