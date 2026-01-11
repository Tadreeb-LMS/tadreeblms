<?php

namespace Database\Seeders\Auth;

use App\Models\Auth\User;
use Database\Seeders\Traits\DisableForeignKeys;
use Illuminate\Database\Seeder;

/**
 * Class UserRoleTableSeeder.
 */
class UserRoleTableSeeder extends Seeder
{
    use DisableForeignKeys;

    /**
     * Run the database seed.
     *
     * @return void
     */
    public function run()
    {
        

        $this->disableForeignKeys();
        User::find(1)->assignRole(config('access.users.admin_role'));
        User::find(2)->assignRole('teacher');
        User::find(3)->assignRole('student');
        //User::find(4)->assignRole(config('access.users.default_role'));
        $this->enableForeignKeys();
    }
}
