<?php

namespace App\Notifications\Backend;

use App\Jobs\SendEmailJob;
use App\Models\UserNotification;

class CertificateNotification
{
    // =========================================================================
    // CERTIFICATE ISSUED
    // =========================================================================

    public static function sendCertificateIssuedEmail($user, $courseName)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Certificate Issued',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Congratulations ' . e($user->full_name) . '!</h3>
                        <p>Your certificate for <strong>' . e($courseName) . '</strong> has been issued.</p>
                        <p>You can download it from your dashboard.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">View Certificate</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Certificate Issued - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createCertificateIssuedBell($user, $courseName)
    {
        $admins = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->where('id', '!=', $user->id)->get();

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'certificate_issued',
                'title' => 'Certificate Issued',
                'message' => $user->full_name . ' received certificate for ' . $courseName . '.',
                'icon' => 'fas fa-certificate',
                'icon_color' => 'success',
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
}
