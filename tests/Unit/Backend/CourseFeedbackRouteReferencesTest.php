<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class CourseFeedbackRouteReferencesTest extends TestCase
{
    /** @test */
    public function course_feedback_question_view_references_defined_routes()
    {
        $routes = file_get_contents(__DIR__.'/../../../routes/backend/admin.php');
        $view = file_get_contents(__DIR__.'/../../../resources/views/backend/course_feedback_question/index.blade.php');

        $this->assertStringContainsString("->name('course-feedback.add-questions')", $routes);
        $this->assertStringContainsString("->name('course-feedback-questions.assigned')", $routes);
        $this->assertStringContainsString("route('admin.course-feedback.add-questions')", $view);
        $this->assertStringContainsString("route('admin.course-feedback-questions.assigned'", $view);
        $this->assertStringNotContainsString("'/admin/course-feedback-questions/assigned/'", $view);
    }
}
