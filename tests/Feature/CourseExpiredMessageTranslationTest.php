<?php

namespace Tests\Feature;

use Tests\TestCase;

class CourseExpiredMessageTranslationTest extends TestCase
{
    /**
     * The expired-course redirect/flash message uses the top-level
     * `course_pages.expired` key. It must resolve in every shipped locale,
     * otherwise the raw key is shown to the user.
     *
     * @test
     */
    public function expired_course_message_resolves_in_every_locale()
    {
        foreach (['en', 'ar', 'es', 'fr', 'it'] as $locale) {
            $message = __('course_pages.expired', [], $locale);

            $this->assertNotSame(
                'course_pages.expired',
                $message,
                "course_pages.expired is not translated for locale [{$locale}]."
            );
            $this->assertNotEmpty($message, "Empty expired message for locale [{$locale}].");
        }

        $this->assertSame(
            'This course enrollment has expired.',
            __('course_pages.expired', [], 'en')
        );
    }
}
