<?php

namespace App\Notifications\Backend;

use App\Jobs\SendEmailJob;
use App\Models\UserNotification;

class UserAuthNotification
{
    // =========================================================================
    // USER CREATED
    // =========================================================================

    public static function sendUserCreatedEmail($user, $label = 'User')
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => 'Welcome to ' . app_name(),
            'sub_heading' => 'Your account has been created as ' . $label,
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>You have been added as a <strong>' . e($label) . '</strong> on ' . app_name() . '.</p>
                        <p>You can now log in and start using the platform.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">Login Now</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Welcome to ' . app_name() . ' - ' . $label,
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createUserCreatedBell($user, $label = 'User')
    {
        $excludeIds = [$user->id];
        if (auth()->check()) {
            $excludeIds[] = auth()->id();
        }
        $admins = self::getAdminsExceptMultiple($excludeIds);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'user_created',
                'title' => 'New ' . $label . ' Created',
                'message' => $user->full_name . ' has been added as a ' . strtolower($label) . '.',
                'icon' => 'fas fa-user-plus',
                'icon_color' => 'success',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // USER UPDATED
    // =========================================================================

    public static function createUserUpdatedBell($user, $label = 'User')
    {
        $excludeIds = [$user->id];
        if (auth()->check()) {
            $excludeIds[] = auth()->id();
        }
        $admins = self::getAdminsExceptMultiple($excludeIds);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'user_updated',
                'title' => $label . ' Updated',
                'message' => $user->full_name . '\'s profile has been updated.',
                'icon' => 'fas fa-user-edit',
                'icon_color' => 'info',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // ROLE ASSIGNED
    // =========================================================================

    public static function sendRoleAssignedEmail($user, $roleName)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Role Assigned',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>You have been assigned the role: <strong>' . e($roleName) . '</strong> on ' . app_name() . '.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">Login Now</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Role Assigned - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createRoleAssignedBell($user, $roleName)
    {
        $excludeIds = [$user->id];
        if (auth()->check()) {
            $excludeIds[] = auth()->id();
        }
        $admins = self::getAdminsExceptMultiple($excludeIds);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'role_assigned',
                'title' => 'Role Assigned',
                'message' => $user->full_name . ' has been assigned the role: ' . $roleName . '.',
                'icon' => 'fas fa-user-tag',
                'icon_color' => 'primary',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // USER DEACTIVATED
    // =========================================================================

    public static function sendUserDeactivatedEmail($user)
    {
        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Account Deactivated',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>Your account has been deactivated on ' . app_name() . '.</p>
                        <p>If you believe this is an error, please contact the administrator.</p>
                        <p>Thank you.</p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Account Deactivated - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createUserDeactivatedBell($user)
    {
        $excludeIds = [$user->id];
        if (auth()->check()) {
            $excludeIds[] = auth()->id();
        }
        $admins = self::getAdminsExceptMultiple($excludeIds);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'user_deactivated',
                'title' => 'User Deactivated',
                'message' => $user->full_name . ' has been deactivated.',
                'icon' => 'fas fa-user-slash',
                'icon_color' => 'danger',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // USER REACTIVATED
    // =========================================================================

    public static function sendUserReactivatedEmail($user)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Account Reactivated',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . '!</h3>
                        <p>Great news! Your account has been reactivated on ' . app_name() . '.</p>
                        <p>You can now log in and access the platform again.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">Login Now</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Account Reactivated - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createUserReactivatedBell($user)
    {
        $excludeIds = [$user->id];
        if (auth()->check()) {
            $excludeIds[] = auth()->id();
        }
        $admins = self::getAdminsExceptMultiple($excludeIds);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'user_activated',
                'title' => 'User Reactivated',
                'message' => $user->full_name . ' has been reactivated.',
                'icon' => 'fas fa-user-check',
                'icon_color' => 'success',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // PASSWORD CHANGED
    // =========================================================================

    public static function sendPasswordChangedEmail($user)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Password Changed',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>Your password has been changed successfully on ' . app_name() . '.</p>
                        <p>If you did not make this change, please contact the administrator immediately.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">Login Now</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Password Changed - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createPasswordChangedBell($user)
    {
        $excludeIds = [$user->id];
        if (auth()->check()) {
            $excludeIds[] = auth()->id();
        }
        $admins = self::getAdminsExceptMultiple($excludeIds);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'password_changed',
                'title' => 'Password Changed',
                'message' => $user->full_name . '\'s password has been changed.',
                'icon' => 'fas fa-key',
                'icon_color' => 'warning',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private static function dispatchEmail($toEmail, $content)
    {
        $html = view('emails.default_email_template', ['content' => $content, 'user' => (object)['email' => $toEmail]])->render();

        dispatch(new SendEmailJob([
            'to_email' => $toEmail,
            'subject' => $content['subject'],
            'html' => $html,
        ]));
    }

    private static function getAdminsExcept($userId)
    {
        return \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->where('id', '!=', $userId)->get();
    }

    private static function getAdminsExceptMultiple(array $userIds)
    {
        return \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->whereNotIn('id', $userIds)->get();
    }
}
