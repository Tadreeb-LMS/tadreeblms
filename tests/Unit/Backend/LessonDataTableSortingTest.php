<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class LessonDataTableSortingTest extends TestCase
{
    public function test_lessons_datatable_uses_qualified_columns_for_course_sorting(): void
    {
        $controller = file_get_contents(
            __DIR__ . '/../../../app/Http/Controllers/Backend/Admin/LessonsController.php'
        );
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/lessons/index.blade.php'
        );

        $this->assertStringContainsString("->select('lessons.*')", $controller);
        $this->assertStringContainsString("->orderBy('lessons.id', 'asc')", $controller);
        $this->assertStringNotContainsString("->orderBy('id', 'asc')", $controller);

        $this->assertStringContainsString("name: 'lessons.id'", $view);
        $this->assertStringContainsString("name: 'lessons.title'", $view);
        $this->assertStringContainsString("name: 'course.title'", $view);
        $this->assertStringContainsString("name: 'lessons.lesson_start_date'", $view);
        $this->assertStringContainsString("name: 'lessons.duration'", $view);
        $this->assertStringContainsString("name: 'lessons.published'", $view);
    }
}
