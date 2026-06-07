<?php

namespace Tests\Unit\Backend;

use PHPUnit\Framework\TestCase;

class LessonCreateFileInputResetTest extends TestCase
{
    /** @test */
    public function adding_another_lesson_resets_cloned_file_inputs(): void
    {
        $view = file_get_contents(
            __DIR__ . '/../../../resources/views/backend/lessons/create.blade.php'
        );

        $this->assertStringContainsString('function resetLessonFileInputs($lesson)', $view);
        $this->assertStringContainsString('$lesson.find(\'input[type="file"]\').each(function ()', $view);
        $this->assertStringContainsString('const $freshInput = $(this).clone(false);', $view);
        $this->assertStringContainsString('$(this).replaceWith($freshInput);', $view);
        $this->assertStringContainsString('$lesson.find(\'.custom-file-label\').each(function ()', $view);
        $this->assertStringContainsString('resetLessonFileInputs(clone);', $view);
    }
}
