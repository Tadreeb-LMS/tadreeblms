<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class LessonCourseColumnLinkTest extends TestCase
{
    public function test_lessons_course_column_renders_clickable_course_link(): void
    {
        $controller = file_get_contents(
            __DIR__ . '/../../../app/Http/Controllers/Backend/Admin/LessonsController.php'
        );

        $this->assertStringContainsString("->editColumn('course'", $controller);
        $this->assertStringContainsString("route('admin.courses.edit'", $controller);
        $this->assertStringContainsString('class="text-primary"', $controller);
        $this->assertStringContainsString('e($q->course->title)', $controller);
        $this->assertStringContainsString(
            "->rawColumns(['lesson_image', 'course', 'qr_code', 'attendance', 'actions'])",
            $controller
        );
    }
}
