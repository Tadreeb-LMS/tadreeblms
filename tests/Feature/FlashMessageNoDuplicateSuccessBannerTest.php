<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * @see resources/views/includes/partials/messages.blade.php - fixed in the
 *      same change to also render session('success'), among other keys.
 *
 * Before that fix, five lesson pages worked around the partial's gap by
 * rendering session('success') inline, immediately before including the
 * shared partial. Once the partial started covering 'success' too, those
 * inline blocks became a second copy of the same banner on the same page.
 * They were removed as part of this fix. This guards against the pattern
 * being reintroduced (by a future edit unaware of why it's gone, or a merge
 * bringing an old copy back), which would silently show a duplicate message
 * rather than break anything visibly obvious.
 */
class FlashMessageNoDuplicateSuccessBannerTest extends TestCase
{
    private const AFFECTED_VIEWS = [
        'resources/views/frontend/courses/lesson.blade.php',
        'resources/views/frontend/courses/lesson-quiz.blade.php',
        'resources/views/frontend/department/lesson.blade.php',
        'resources/views/frontend-rtl/courses/lesson.blade.php',
        'resources/views/frontend-rtl/courses/lesson-quiz.blade.php',
    ];

    #[Test]
    public function views_that_include_the_shared_messages_partial_do_not_also_render_session_success_inline()
    {
        foreach (self::AFFECTED_VIEWS as $relativePath) {
            $path = base_path($relativePath);
            $this->assertFileExists($path);

            $source = file_get_contents($path);

            $this->assertStringContainsString(
                "@include('includes.partials.messages')",
                $source,
                "{$relativePath} no longer includes the shared messages partial - was it moved or renamed?"
            );

            $this->assertDoesNotMatchRegularExpression(
                "/session\\(\\)->has\\('success'\\)[\\s\\S]{0,400}session\\('success'\\)/",
                $source,
                "{$relativePath} renders session('success') inline as well as through the shared partial "
                    . '(includes.partials.messages already covers it) - this duplicates the success banner.'
            );
        }
    }
}
