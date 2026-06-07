<?php

namespace Tests\Unit\Frontend;

use PHPUnit\Framework\TestCase;

class LessonSingleActiveVideoTest extends TestCase
{
    /** @test */
    public function lesson_media_players_pause_other_videos_when_playback_starts(): void
    {
        $pausePartial = file_get_contents(
            __DIR__ . '/../../../resources/views/frontend/courses/partials/pause-inactive-media.blade.php'
        );

        $lessonView = file_get_contents(
            __DIR__ . '/../../../resources/views/frontend/courses/lesson.blade.php'
        );

        $rtlLessonView = file_get_contents(
            __DIR__ . '/../../../resources/views/frontend-rtl/courses/lesson.blade.php'
        );

        $this->assertStringContainsString('window.registerExclusiveLessonMediaPlayer', $pausePartial);
        $this->assertStringContainsString('window.pauseOtherLessonMedia', $pausePartial);
        $this->assertStringContainsString("document.addEventListener('play'", $pausePartial);
        $this->assertStringContainsString("document.addEventListener('playing'", $pausePartial);
        $this->assertStringContainsString("playerInstance.on('play'", $pausePartial);
        $this->assertStringContainsString('playerInstance.media === activeMedia', $pausePartial);

        $this->assertStringContainsString('window.registerExclusiveLessonMediaPlayer(playerInstance)', $lessonView);
        $this->assertStringContainsString('window.registerExclusiveLessonMediaPlayer(playerInstance)', $rtlLessonView);
    }
}
