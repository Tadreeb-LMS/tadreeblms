<?php

namespace App\Notifications\Backend;

use App\Jobs\SendEmailJob;
use App\Models\UserNotification;

class LessonNotification
{
    // =========================================================================
    // LESSON ADDED
    // =========================================================================

    public static function sendLessonAddedEmail($creator, $lesson, $course)
    {
        $admins = self::getAdminsExcept($creator->id);

        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'New Lesson Added',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>A new lesson has been added:</p>
                            <p><strong>Lesson:</strong> ' . e($lesson->title) . '</p>
                            <p><strong>Course:</strong> ' . e($course->title ?? 'N/A') . '</p>
                            <p><strong>Added by:</strong> ' . e($creator->full_name) . '</p>
                            <p><strong>Date:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'New Lesson Added - ' . ($lesson->title ?? app_name()),
            ];

            self::dispatchEmail($admin->email, $content);
        }
    }

    public static function createLessonAddedBell($creator, $lesson, $course)
    {
        $admins = self::getAdminsExcept($creator->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'lesson_added',
                'title' => 'New Lesson Added',
                'message' => e($creator->full_name) . ' added a new lesson "' . e($lesson->title ?? 'Untitled') . '" to course "' . e($course->title ?? 'N/A') . '".',
                'icon' => 'fas fa-file-alt',
                'icon_color' => 'info',
                'link' => route('admin.courses.edit', $course->id ?? 0),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // LESSON UPDATED
    // =========================================================================

    public static function sendLessonUpdatedEmail($updater, $lesson, $course)
    {
        $admins = self::getAdminsExcept($updater->id);

        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'Lesson Updated',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>A lesson has been updated:</p>
                            <p><strong>Lesson:</strong> ' . e($lesson->title) . '</p>
                            <p><strong>Course:</strong> ' . e($course->title ?? 'N/A') . '</p>
                            <p><strong>Updated by:</strong> ' . e($updater->full_name) . '</p>
                            <p><strong>Date:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'Lesson Updated - ' . ($lesson->title ?? app_name()),
            ];

            self::dispatchEmail($admin->email, $content);
        }
    }

    public static function createLessonUpdatedBell($updater, $lesson, $course)
    {
        $admins = self::getAdminsExcept($updater->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'lesson_updated',
                'title' => 'Lesson Updated',
                'message' => e($updater->full_name) . ' updated the lesson "' . e($lesson->title ?? 'Untitled') . '" in course "' . e($course->title ?? 'N/A') . '".',
                'icon' => 'fas fa-edit',
                'icon_color' => 'warning',
                'link' => route('admin.courses.edit', $course->id ?? 0),
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
