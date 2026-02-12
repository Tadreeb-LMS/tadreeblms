<?php

namespace App\Notifications\Backend;

use App\Jobs\SendEmailJob;
use App\Models\UserNotification;

class AssessmentNotification
{
    // =========================================================================
    // ASSESSMENT ASSIGNED
    // =========================================================================

    public static function sendAssessmentAssignedEmail($user, $assessmentTitle, $dueDate)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'New Assessment Assigned',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>You have been assigned a new assessment: <strong>' . e($assessmentTitle) . '</strong>.</p>
                        <p>Due date: <strong>' . e($dueDate) . '</strong></p>
                        <p>Please log in to complete the assessment before the due date.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">Login Now</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'New Assessment Assigned - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createAssessmentAssignedBell($user, $assessmentTitle)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'assessment_assigned',
                'title' => 'Assessment Assigned',
                'message' => $assessmentTitle . ' assigned to ' . $user->full_name . '.',
                'icon' => 'fas fa-clipboard-check',
                'icon_color' => 'primary',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // ASSESSMENT SUBMITTED
    // =========================================================================

    public static function createAssessmentSubmittedBell($user, $courseName)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'assessment_submitted',
                'title' => 'Assessment Submitted',
                'message' => $user->full_name . ' has submitted assessment for ' . $courseName . '.',
                'icon' => 'fas fa-clipboard-check',
                'icon_color' => 'info',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // ASSESSMENT GRADED
    // =========================================================================

    public static function sendAssessmentGradedEmail($user, $courseName, $scorePercent, $status)
    {
        $loginUrl = route('frontend.auth.login');

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Assessment Result',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>Your assessment for <strong>' . e($courseName) . '</strong> has been graded.</p>
                        <p>Score: <strong>' . e($scorePercent) . '%</strong></p>
                        <p>Status: <strong>' . e($status) . '</strong></p>
                        <p style="margin-top: 20px;">
                            <a href="' . $loginUrl . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">View Details</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Assessment Result - ' . app_name(),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createAssessmentGradedBell($user, $courseName, $scorePercent, $status)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'assessment_graded',
                'title' => 'Assessment Graded',
                'message' => $user->full_name . ' scored ' . $scorePercent . '% (' . $status . ') on ' . $courseName . '.',
                'icon' => 'fas fa-clipboard-check',
                'icon_color' => $status === 'Passed' ? 'success' : 'danger',
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
