<?php

namespace Tests\Feature\Backend\User;

use Tests\TestCase;
use App\Models\Auth\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BulkUpdateUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function bulk_update_is_rejected_when_no_fields_are_changed()
    {
        $this->loginAsAdmin();
        $user = factory(User::class)->create(['confirmed' => 0]);

        $response = $this->post(route('admin.auth.users.bulk.update'), [
            'user_ids' => [$user->id],
            'department_id' => '',
            'confirmed' => '',
            'role' => '',
        ]);

        $response->assertStatus(422);
        $response->assertJson(['success' => false]);
        $this->assertEquals(0, $user->fresh()->confirmed);
    }

    /** @test */
    public function bulk_update_applies_a_changed_confirmed_value()
    {
        $this->loginAsAdmin();
        $user = factory(User::class)->create(['confirmed' => 0]);

        $response = $this->post(route('admin.auth.users.bulk.update'), [
            'user_ids' => [$user->id],
            'department_id' => '',
            'confirmed' => '1',
            'role' => '',
        ]);

        $response->assertStatus(200);
        $this->assertEquals(1, $user->fresh()->confirmed);
    }
}
