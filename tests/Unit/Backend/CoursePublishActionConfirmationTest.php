<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class CoursePublishActionConfirmationTest extends TestCase
{
    public function test_course_unpublish_action_uses_confirmation_modal(): void
    {
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/datatable/action-unpublish.blade.php'
        );

        $this->assertStringContainsString('name="confirm_item"', $view);
        $this->assertStringContainsString(
            'Are you sure you want to unpublish this course? This will make the course invisible to trainees.',
            $view
        );
        $this->assertStringContainsString('data-trans-button-confirm', $view);
    }

    public function test_course_publish_action_uses_confirmation_modal(): void
    {
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/datatable/action-publish.blade.php'
        );

        $this->assertStringContainsString('name="confirm_item"', $view);
        $this->assertStringContainsString(
            'Are you sure you want to publish this course? This will make the course visible to trainees.',
            $view
        );
        $this->assertStringContainsString('data-trans-button-confirm', $view);
    }
}
