<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class CourseEnrollmentNotificationTest extends TestCase
{
    /** @test */
    public function course_enrollment_creates_student_facing_bell_notifications(): void
    {
        $notification = file_get_contents(
            __DIR__ . '/../../../app/Notifications/Backend/CourseNotification.php'
        );

        $assessmentController = file_get_contents(
            __DIR__ . '/../../../app/Http/Controllers/Backend/Admin/AssessmentAccountsController.php'
        );

        $coursesController = file_get_contents(
            __DIR__ . '/../../../app/Http/Controllers/Backend/Admin/CoursesController.php'
        );

        $header = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/includes/header.blade.php'
        );

        $this->assertStringContainsString('function createCourseEnrollmentBell($user, $course)', $notification);
        $this->assertStringContainsString("'user_id' => \$user->id", $notification);
        $this->assertStringContainsString("'type' => 'course_enrollment'", $notification);
        $this->assertStringContainsString('You have been enrolled in a course:', $notification);
        $this->assertStringContainsString("route('courses.show', \$course->slug)", $notification);

        $this->assertSame(
            4,
            substr_count($assessmentController, 'CourseNotification::createCourseEnrollmentBell($emp, $course);')
        );

        $this->assertSame(
            2,
            substr_count($coursesController, 'CourseNotification::createCourseEnrollmentBell($student, $course);')
        );

        $this->assertStringContainsString('$subscription->wasRecentlyCreated', $coursesController);

        $this->assertStringContainsString('$bellUnreadNotificationCount', $header);
        $this->assertStringContainsString('UserNotification::forUser(auth()->id())->unread()', $header);
        $this->assertStringContainsString('@forelse($bellUnreadNotifications as $notification)', $header);
        $this->assertStringContainsString('unreadNotificationCounter', $header);
    }
}
