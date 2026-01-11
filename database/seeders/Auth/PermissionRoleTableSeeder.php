<?php

namespace Database\Seeders\Auth;

use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;

class PermissionRoleTableSeeder extends Seeder
{
    use DisableForeignKeys;

    public function run()
    {
        $this->disableForeignKeys();

        /*
        |--------------------------------------------------------------------------
        | Roles
        |--------------------------------------------------------------------------
        */
        $admin   = Role::firstOrCreate(['name' => config('access.users.admin_role')]);
        $teacher = Role::firstOrCreate(['name' => 'teacher']);
        $student = Role::firstOrCreate(['name' => 'student']);
        //$user    = Role::firstOrCreate(['name' => 'user']);

        /*
        |--------------------------------------------------------------------------
        | Permissions (Module-based)
        |--------------------------------------------------------------------------
        */
        $modules = [
            'trainer',
            'trainee',
            'calender',
            'learning_pathway',
            'reports',
            'site_management',
            'access_management',
            'settings',
            'send_email_notification',
            //'user',
            'user_management',
            'permission',
            'role',
            'course',
            'lesson',
            'question',
            'backend',
            'contact_request',
            'employee_request',
            'course_assignment',
            'course_invitation',
            'course_test',
            'lesson_questions',
            'feedback',
            'feedback_questions',
            'course_assesment',
            'course_manual_assesment'
        ];

        $actions = ['access', 'create', 'edit', 'view', 'delete'];

        //truncate all permissions
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        foreach ($modules as $module) {
            foreach ($actions as $action) {
                Permission::firstOrCreate([
                    'name' => "{$module}_{$action}",
                    'guard_name' => 'web',
                ]);
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Role → Permission Mapping
        |--------------------------------------------------------------------------
        */

        // Admin → all permissions
        $admin->syncPermissions(Permission::all());

        // Teacher → limited permissions
        $teacher->syncPermissions([
            'backend_view',
            'course_access',
            'course_create',
            'course_edit',
            'course_view',
            'lesson_access',
            'lesson_create',
            'lesson_edit',
            'lesson_view',
            'question_access',
            'question_create',
            'question_edit',
            'question_view',
        ]);

        // Student → backend view only
        $student->syncPermissions([
            'backend_view',
        ]);

        $this->enableForeignKeys();
    }
}
