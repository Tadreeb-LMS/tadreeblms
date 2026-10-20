<?php

namespace Tests\Feature;

use Tests\TestCase;

class QuestionOptionsLabelTest extends TestCase
{
    /** @test */
    public function options_label_is_required_and_has_no_icon()
    {
        $view = file_get_contents(resource_path('views/backend/test_questions/create.blade.php'));

        $this->assertSame(
            1,
            preg_match('/<label>\s*\{\{ __\(\'labels\.backend\.questions\.options\'\) \}\}\s*<span style="color:red">\*<\/span>\s*<\/label>/', $view),
            'Options label must show the required asterisk.'
        );

        $this->assertDoesNotMatchRegularExpression(
            '/<label>\s*<i [^>]*>\s*<\/i>\s*\{\{ __\(\'labels\.backend\.questions\.options\'\) \}\}/',
            $view,
            'Options label must not have a leading icon.'
        );
    }
}
