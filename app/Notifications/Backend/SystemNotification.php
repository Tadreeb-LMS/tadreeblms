<?php

namespace App\Notifications\Backend;

use App\Models\UserNotification;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class SystemNotification
{
    // =========================================================================
    // ADMIN LOGIN ALERT
    // =========================================================================

    public static function sendAdminLoginEmail($adminUser, $ipAddress)
    {
        $admins = self::getAdminsExcept($adminUser->id);

        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'Admin Login Alert',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>An admin account has logged in:</p>
                            <p><strong>User:</strong> ' . e($adminUser->full_name) . ' (' . e($adminUser->email) . ')</p>
                            <p><strong>IP Address:</strong> ' . e($ipAddress) . '</p>
                            <p><strong>Time:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>
                            <p>If this was not expected, please investigate immediately.</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'Admin Login Alert - ' . app_name(),
            ];

            Mail::send('emails.default_email_template', ['content' => $content, 'user' => $admin], function ($message) use ($admin, $content) {
                $message->to($admin->email)->subject($content['subject']);
            });
        }
    }

    public static function createAdminLoginBell($adminUser, $ipAddress)
    {
        $admins = self::getAdminsExcept($adminUser->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'admin_login',
                'title' => 'Admin Login',
                'message' => $adminUser->full_name . ' logged in from IP ' . $ipAddress . '.',
                'icon' => 'fas fa-sign-in-alt',
                'icon_color' => 'info',
                'link' => route('admin.auth.user.show', $adminUser->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // FAILED LOGIN
    // =========================================================================

    public static function sendFailedLoginEmail($email, $ipAddress)
    {
        $admins = self::getAllAdmins();

        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'Failed Login Attempt',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>A failed login attempt has been detected:</p>
                            <p><strong>Email used:</strong> ' . e($email) . '</p>
                            <p><strong>IP Address:</strong> ' . e($ipAddress) . '</p>
                            <p><strong>Time:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>
                            <p>If this looks suspicious, please investigate immediately.</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'Failed Login Attempt - ' . app_name(),
            ];

            Mail::send('emails.default_email_template', ['content' => $content, 'user' => $admin], function ($message) use ($admin, $content) {
                $message->to($admin->email)->subject($content['subject']);
            });
        }
    }

    public static function createFailedLoginBell($email, $ipAddress)
    {
        $admins = self::getAllAdmins();

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'failed_login',
                'title' => 'Failed Login Attempt',
                'message' => 'Failed login attempt for "' . e($email) . '" from IP ' . e($ipAddress) . '.',
                'icon' => 'fas fa-exclamation-triangle',
                'icon_color' => 'danger',
                'link' => null,
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // SYSTEM ERROR (commented out — not needed)
    // =========================================================================

    // public static function sendSystemErrorEmail($exception)
    // {
    //     $errorKey = 'system_error_email_' . md5($exception->getMessage() . $exception->getFile());
    //     if (Cache::has($errorKey)) {
    //         return;
    //     }
    //     Cache::put($errorKey, true, 3600);
    //
    //     $url = request()->fullUrl() ?? 'N/A';
    //     $method = request()->method() ?? 'N/A';
    //     $userAgent = request()->userAgent() ?? 'N/A';
    //     $ip = request()->ip() ?? 'N/A';
    //     $loggedUser = auth()->check() ? auth()->user()->full_name . ' (' . auth()->user()->email . ')' : 'Guest';
    //     $stackTrace = $exception->getTraceAsString();
    //
    //     $admins = self::getAllAdmins();
    //
    //     foreach ($admins as $admin) {
    //         $content = [
    //             'email_heading' => app_name(),
    //             'sub_heading' => 'System Error Alert',
    //             'email_content' => '...',
    //             'subject' => 'System Error - ' . e(Str::limit($exception->getMessage(), 80)) . ' - ' . app_name(),
    //         ];
    //
    //         Mail::send('emails.default_email_template', ['content' => $content, 'user' => $admin], function ($message) use ($admin, $content) {
    //             $message->to($admin->email)->subject($content['subject']);
    //         });
    //     }
    // }

    // public static function createSystemErrorBell($exception)
    // {
    //     $errorKey = 'system_error_bell_' . md5($exception->getMessage() . $exception->getFile());
    //     if (Cache::has($errorKey)) {
    //         return;
    //     }
    //     Cache::put($errorKey, true, 3600);
    //
    //     $file = basename($exception->getFile()) . ':' . $exception->getLine();
    //
    //     $admins = self::getAllAdmins();
    //
    //     foreach ($admins as $admin) {
    //         UserNotification::create([
    //             'user_id' => $admin->id,
    //             'type' => 'system_error',
    //             'title' => 'System Error',
    //             'message' => Str::limit($exception->getMessage(), 150) . ' (' . $file . ')',
    //             'icon' => 'fas fa-exclamation-circle',
    //             'icon_color' => 'danger',
    //             'link' => '#',
    //             'is_read' => false,
    //         ]);
    //     }
    // }

    // =========================================================================
    // HELPERS
    // =========================================================================

    private static function getAdminsExcept($userId)
    {
        return \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->where('id', '!=', $userId)->get();
    }

    private static function getAllAdmins()
    {
        return \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->get();
    }
}
