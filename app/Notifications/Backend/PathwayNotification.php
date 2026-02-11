<?php

namespace App\Notifications\Backend;

use App\Jobs\SendEmailJob;
use App\Models\UserNotification;

class PathwayNotification
{
    // =========================================================================
    // PATHWAY ASSIGNED
    // =========================================================================

    public static function createPathwayAssignedBell($user, $pathwayTitle)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'pathway_assigned',
                'title' => 'Pathway Assigned',
                'message' => $pathwayTitle . ' assigned to ' . $user->full_name . '.',
                'icon' => 'fas fa-road',
                'icon_color' => 'primary',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // PATHWAY COMPLETED
    // =========================================================================

    public static function sendPathwayCompletedEmail($user, $pathwayTitle)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Learning Pathway Completed',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Congratulations ' . e($user->full_name) . '!</h3>
                        <p>You have successfully completed the learning pathway: <strong>' . e($pathwayTitle) . '</strong>.</p>
                        <p>Keep up the great work!</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">View Dashboard</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Learning Pathway Completed - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createPathwayCompletedBell($user, $pathwayTitle)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'pathway_completed',
                'title' => 'Pathway Completed',
                'message' => $user->full_name . ' has completed ' . $pathwayTitle . '.',
                'icon' => 'fas fa-road',
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

    private static function getAdminsExcept($userId)
    {
        return \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->where('id', '!=', $userId)->get();
    }
}
