<?php

namespace Tests\Feature\Backend;

use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DisabledPositionsModuleTest extends TestCase
{
    /** @test */
    public function positions_sidebar_link_is_not_rendered_for_admin_users()
    {
        $adminRole = $this->getAdminRole();
        // category_access is required for the admin dashboard to render the General sidebar section.
        $adminRole->givePermissionTo(Permission::findOrCreate('category_access'));

        $this->loginAsAdmin();

        // Assert by the route URL so we don't false-positive on the word "Positions"
        // appearing elsewhere on the dashboard (titles, widgets, etc.).
        $this->get(route('admin.dashboard'))
            ->assertOk()
            ->assertDontSee(route('admin.position.index'));
    }

    /** @test */
    public function positions_get_routes_are_disabled_for_direct_access()
    {
        $this->loginAsAdmin();

        $this->get(route('admin.position.index'))->assertNotFound();
        $this->get(route('admin.position.create'))->assertNotFound();
        $this->get(route('admin.position.show', ['page' => 1]))->assertNotFound();
        $this->get(route('admin.position.edit', ['page' => 1]))->assertNotFound();
        $this->get(route('admin.position.get_data'))->assertNotFound();
    }

    /** @test */
    public function positions_post_routes_are_disabled_for_direct_access()
    {
        $this->loginAsAdmin();

        $this->post(route('admin.position.store'))->assertNotFound();
        $this->post(route('admin.position.update', ['page' => 1]))->assertNotFound();
        $this->post(route('admin.position.add.import'))->assertNotFound();
        $this->post(route('admin.position.mass_destroy'))->assertNotFound();
        $this->post(route('admin.position.restore', ['page' => 1]))->assertNotFound();
    }

    /** @test */
    public function positions_delete_routes_are_disabled_for_direct_access()
    {
        $this->loginAsAdmin();

        $this->delete(route('admin.position.destroy', ['page' => 1]))->assertNotFound();
        $this->delete(route('admin.position.perma_del', ['page' => 1]))->assertNotFound();
    }
}
