<?php

namespace Tests\Feature\Backend\Admin;

use App\Jobs\BulkEmailDispatchJob;
use App\Models\Auth\User;
use App\Models\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class EmailNotificationControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mail.mailers.smtp.host' => '127.0.0.1',
            'mail.mailers.smtp.port' => 1025,
        ]);

        putenv('MAIL_FROM_ADDRESS=tests@example.com');
        $_ENV['MAIL_FROM_ADDRESS'] = 'tests@example.com';
        $_SERVER['MAIL_FROM_ADDRESS'] = 'tests@example.com';
    }

    /** @test */
    public function department_mode_returns_department_error_when_no_recipients_mapped()
    {
        $this->loginAsAdmin();

        $department = Department::create([
            'user_id' => 1,
            'title' => 'Engineering',
            'slug' => 'engineering',
        ]);

        $response = $this->postJson('/user/send-email-notification', [
            'recipient_mode' => 'department',
            'department_id' => $department->id,
            'subject' => 'Department Notice',
            'register_button' => 'https://example.com/register',
            'email_content' => 'Test message',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['department_id'])
            ->assertJsonMissingValidationErrors(['users']);
    }

    /** @test */
    public function users_mode_returns_users_error_when_no_users_selected()
    {
        $this->loginAsAdmin();

        $response = $this->postJson('/user/send-email-notification', [
            'recipient_mode' => 'users',
            'subject' => 'Users Notice',
            'register_button' => 'https://example.com/register',
            'email_content' => 'Test message',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['users']);
    }

    /** @test */
    public function department_mode_dispatches_email_job_when_recipients_exist()
    {
        Queue::fake();

        $this->loginAsAdmin();

        $department = Department::create([
            'user_id' => 1,
            'title' => 'HR',
            'slug' => 'hr',
        ]);

        $recipient = factory(User::class)->create([
            'email' => 'dept-user@example.com',
            'active' => 1,
        ]);

        DB::table('employee_profiles')->insert([
            'user_id' => $recipient->id,
            'department' => $department->id,
            'position' => 'Coordinator',
            'payment_method' => 'bank',
            'payment_details' => 'N/A',
            'description' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->postJson('/user/send-email-notification', [
            'recipient_mode' => 'department',
            'department_id' => $department->id,
            'subject' => 'HR Notice',
            'register_button' => 'https://example.com/register',
            'email_content' => 'Welcome HR team',
        ]);

        $response->assertOk()
            ->assertJsonFragment([
                'message' => __('admin_pages.email_notifications.messages.notification_queued_successfully'),
            ]);

        Queue::assertPushed(BulkEmailDispatchJob::class, 1);
        $this->assertDatabaseHas('email_campain_users', [
            'email' => 'dept-user@example.com',
            'status' => 'in-queue',
        ]);
    }
}
