<?php

namespace Tests\Feature\Backend\User;

use Tests\TestCase;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ImpersonateUserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function an_admin_can_impersonate_other_users()
    {
        config(['access.impersonation' => true]);
        $user = factory(User::class)->create();
        $admin = $this->loginAsAdmin();

        $this->assertAuthenticatedAs($admin);

        $this->get("/admin/auth/user/{$user->id}/login-as");

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function an_admin_can_exit_an_impersonation()
    {
        config(['access.impersonation' => true]);
        $user = factory(User::class)->create();
        $admin = $this->loginAsAdmin();

        $this->assertAuthenticatedAs($admin);

        $this->get("/admin/auth/user/{$user->id}/login-as");

        $this->assertAuthenticatedAs($user);

        $this->get('/logout-as');

        $this->assertAuthenticatedAs($admin);
    }

    /** @test */
    public function impersonation_of_himself_does_not_work()
    {
        config(['access.impersonation' => true]);
        $admin = $this->loginAsAdmin();

        $response = $this->get("/admin/auth/user/{$admin->id}/login-as");

        $response->assertSessionHas(['flash_danger' => 'Do not try to login as yourself.']);
    }

    /** @test */
    public function impersonation_is_disabled_by_default()
    {
        $user = factory(User::class)->create();
        $this->loginAsAdmin();

        $response = $this->get("/admin/auth/user/{$user->id}/login-as");

        $response->assertStatus(500); // or whatever GeneralException returns, usually 500 or it might be caught by handler
    }
}
