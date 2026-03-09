<?php

namespace App\Notifications\Backend;

use App\Jobs\SendEmailJob;
use App\Models\UserNotification;

class CourseNotification
{
    // =========================================================================
    // COURSE CREATED
    // =========================================================================

    public static function sendCourseCreatedEmail($creator, $course)
    {
        $admins = self::getAdminsExcept($creator->id);

        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'New Course Created',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>A new course has been created:</p>
                            <p><strong>Course:</strong> ' . e($course->title) . '</p>
                            <p><strong>Created by:</strong> ' . e($creator->full_name) . '</p>
                            <p><strong>Date:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'New Course Created - ' . ($course->title ?? app_name()),
            ];

            self::dispatchEmail($admin->email, $content);
        }
    }

    public static function createCourseCreatedBell($creator, $course)
    {
        $admins = self::getAdminsExcept($creator->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'course_created',
                'title' => 'New Course Created',
                'message' => e($creator->full_name) . ' created a new course: ' . e($course->title ?? 'Untitled') . '.',
                'icon' => 'fas fa-plus-circle',
                'icon_color' => 'success',
                'link' => route('admin.courses.edit', $course->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // COURSE PUBLISHED / UNPUBLISHED
    // =========================================================================

    public static function sendCoursePublishedEmail($user, $course, $isPublished)
    {
        $status = $isPublished ? 'Published' : 'Unpublished';
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'Course ' . $status,
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>A course has been <strong>' . strtolower($status) . '</strong>:</p>
                            <p><strong>Course:</strong> ' . e($course->title) . '</p>
                            <p><strong>' . $status . ' by:</strong> ' . e($user->full_name) . '</p>
                            <p><strong>Date:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'Course ' . $status . ' - ' . ($course->title ?? app_name()),
            ];

            self::dispatchEmail($admin->email, $content);
        }
    }

    public static function createCoursePublishedBell($user, $course, $isPublished)
    {
        $status = $isPublished ? 'Published' : 'Unpublished';
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'course_published',
                'title' => 'Course ' . $status,
                'message' => e($user->full_name) . ' ' . strtolower($status) . ' the course: ' . e($course->title ?? 'Untitled') . '.',
                'icon' => $isPublished ? 'fas fa-eye' : 'fas fa-eye-slash',
                'icon_color' => $isPublished ? 'success' : 'warning',
                'link' => route('admin.courses.edit', $course->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // COURSE EXPIRED
    // =========================================================================

    public static function sendCourseExpiredEmail($course)
    {
        // Notify admins
        $admins = self::getAllAdmins();
        foreach ($admins as $admin) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'Course Expired',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($admin->full_name) . ',</h3>
                            <p>A course has expired:</p>
                            <p><strong>Course:</strong> ' . e($course->title) . '</p>
                            <p><strong>Expired on:</strong> ' . e($course->expire_at) . '</p>
                            <p>Please review and take necessary action.</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'Course Expired - ' . ($course->title ?? app_name()),
            ];

            self::dispatchEmail($admin->email, $content);
        }

        // Notify assigned trainees
        $assignees = self::getCourseAssignees($course->id);
        foreach ($assignees as $user) {
            $content = [
                'email_heading' => app_name(),
                'sub_heading' => 'Course Expired',
                'email_content' => '<table>
                    <tr>
                        <td>
                            <h3>Hello ' . e($user->full_name) . ',</h3>
                            <p>The course <strong>' . e($course->title) . '</strong> that was assigned to you has expired on <strong>' . e($course->expire_at) . '</strong>.</p>
                            <p>Please contact your administrator for further instructions.</p>
                        </td>
                    </tr>
                </table>',
                'subject' => 'Course Expired - ' . ($course->title ?? app_name()),
            ];

            self::dispatchEmail($user->email, $content);
        }
    }

    public static function createCourseExpiredBell($course)
    {
        // Bell for admins
        $admins = self::getAllAdmins();
        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'course_expired',
                'title' => 'Course Expired',
                'message' => 'The course "' . e($course->title ?? 'Untitled') . '" has expired.',
                'icon' => 'fas fa-calendar-times',
                'icon_color' => 'danger',
                'link' => route('admin.courses.edit', $course->id),
                'is_read' => false,
            ]);
        }

        // Bell for assigned trainees
        $assignees = self::getCourseAssignees($course->id);
        foreach ($assignees as $user) {
            UserNotification::create([
                'user_id' => $user->id,
                'type' => 'course_expired',
                'title' => 'Course Expired',
                'message' => 'The course "' . e($course->title ?? 'Untitled') . '" that was assigned to you has expired.',
                'icon' => 'fas fa-calendar-times',
                'icon_color' => 'danger',
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // COURSE ASSIGNED
    // =========================================================================

    public static function createCourseAssignedBell($user, $course)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'course_assigned',
                'title' => 'Course Assigned',
                'message' => ($course->title ?? 'A course') . ' has been assigned to ' . $user->full_name . '.',
                'icon' => 'fas fa-book',
                'icon_color' => 'primary',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // COURSE COMPLETED
    // =========================================================================

    public static function sendCourseCompletedEmail($user, $course)
    {
        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Course Completed',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Congratulations ' . e($user->full_name) . '!</h3>
                        <p>You have successfully completed the course: <strong>' . e($course->title) . '</strong>.</p>
                        <p>Keep up the great work!</p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Course Completed - ' . ($course->title ?? app_name()),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createCourseCompletedBell($user, $course)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'course_completed',
                'title' => 'Course Completed',
                'message' => $user->full_name . ' has completed ' . ($course->title ?? 'a course') . '.',
                'icon' => 'fas fa-check-circle',
                'icon_color' => 'success',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // COURSE DUE REMINDER (3 days before due date)
    // =========================================================================

    public static function sendCourseDueReminderEmail($user, $course, $dueDate)
    {
        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Course Due Reminder',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($user->full_name) . ',</h3>
                        <p>This is a reminder that your course <strong>' . e($course->title) . '</strong> is due on <strong>' . e($dueDate) . '</strong>.</p>
                        <p>Please complete the course before the due date.</p>
                        <p style="margin-top: 20px;">
                            <a href="' . url('/course/' . $course->slug) . '" style="display: inline-block; background-color: #3c4085; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; font-weight: bold; font-size: 16px;">Go to Course</a>
                        </p>
                    </td>
                </tr>
            </table>',
            'subject' => 'Course Due Reminder - ' . ($course->title ?? app_name()),
        ];

        self::dispatchEmail($user->email, $content);
    }

    public static function createCourseDueReminderBell($user, $course, $dueDate)
    {
        $admins = self::getAdminsExcept($user->id);

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'course_due_reminder',
                'title' => 'Course Due Reminder',
                'message' => $user->full_name . '\'s course ' . ($course->title ?? '') . ' is due on ' . $dueDate . '.',
                'icon' => 'fas fa-clock',
                'icon_color' => 'warning',
                'link' => route('admin.auth.user.show', $user->id),
                'is_read' => false,
            ]);
        }
    }

    // =========================================================================
    // TRAINER ASSIGNED
    // =========================================================================

    public static function sendTrainerAssignedEmail($trainer, $course, $assignedBy)
    {
        $meetingDetails = '';
        if ($course->meeting_start_at) {
            $meetingDate = \Carbon\Carbon::parse($course->meeting_start_at)->format('F j, Y g:i A');
            $meetingDetails = '
                <h4 style="margin-top: 20px; color: #333;">Meeting Details</h4>
                <p><strong>Date & Time:</strong> ' . e($meetingDate) . ' (' . e($course->meeting_timezone) . ')</p>
                <p><strong>Duration:</strong> ' . e($course->meeting_duration) . ' minutes</p>';

            if ($course->meeting_host_url) {
                $meetingDetails .= '
                    <p style="margin-top: 15px;">
                        <a href="' . e($course->meeting_host_url) . '" style="display: inline-block; padding: 12px 30px; background: linear-gradient(90deg, #223a6a, #cc8a03); color: white; text-decoration: none; border-radius: 30px; font-size: 16px;">Start the Meeting as Host</a>
                    </p>
                    <p>Or copy and paste this link into your browser to start the meeting:</p>
                    <p><a href="' . e($course->meeting_host_url) . '" style="color: #223a6a;">' . e($course->meeting_host_url) . '</a></p>';
            } elseif ($course->meeting_join_url) {
                $meetingDetails .= '
                    <p style="margin-top: 15px;">
                        <a href="' . e($course->meeting_join_url) . '" style="display: inline-block; padding: 12px 30px; background-color: #28a745; color: white; text-decoration: none; border-radius: 5px;">Join the Meeting</a>
                    </p>
                    <p><a href="' . e($course->meeting_join_url) . '" style="font-size: 0.9em; word-break: break-all; color: #223a6a;">' . e($course->meeting_join_url) . '</a></p>';
            }
        }

        $content = [
            'email_heading' => app_name(),
            'sub_heading' => 'Trainer Assigned to Course',
            'email_content' => '<table>
                <tr>
                    <td>
                        <h3>Hello ' . e($trainer->full_name) . ',</h3>
                        <p>You have been assigned as a trainer to the following course:</p>
                        <p><strong>Course:</strong> ' . e($course->title) . '</p>
                        <p><strong>Assigned by:</strong> ' . e($assignedBy->full_name) . '</p>
                        <p><strong>Date:</strong> ' . e(now()->format('d M Y, h:i A')) . '</p>' . 
                        $meetingDetails . '
                    </td>
                </tr>
            </table>',
            'subject' => 'Trainer Assignment - ' . ($course->title ?? app_name()),
        ];

        self::dispatchEmail($trainer->email, $content);
    }

    public static function createTrainerAssignedBell($trainer, $course, $assignedBy)
    {
        UserNotification::create([
            'user_id' => $trainer->id,
            'type' => 'trainer_assigned',
            'title' => 'Trainer Assigned to Course',
            'message' => 'You have been assigned as a trainer to "' . e($course->title ?? 'a course') . '".',
            'icon' => 'fas fa-chalkboard-teacher',
            'icon_color' => 'primary',
            'link' => route('admin.courses.edit', $course->id),
            'is_read' => false,
        ]);

        $admins = \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->where('id', '!=', $assignedBy->id)->where('id', '!=', $trainer->id)->get();

        foreach ($admins as $admin) {
            UserNotification::create([
                'user_id' => $admin->id,
                'type' => 'trainer_assigned',
                'title' => 'Trainer Assigned',
                'message' => e($trainer->full_name) . ' has been assigned as trainer to "' . e($course->title ?? 'a course') . '".',
                'icon' => 'fas fa-chalkboard-teacher',
                'icon_color' => 'primary',
                'link' => route('admin.courses.edit', $course->id),
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

    private static function getAllAdmins()
    {
        return \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('name', config('access.users.admin_role'));
        })->get();
    }

    private static function getCourseAssignees($courseId)
    {
        $userIds = \App\Models\Stripe\SubscribeCourse::where('course_id', $courseId)
            ->where('is_completed', 0)
            ->whereNull('deleted_at')
            ->pluck('user_id');

        return \App\Models\Auth\User::whereIn('id', $userIds)->get();
    }
}
