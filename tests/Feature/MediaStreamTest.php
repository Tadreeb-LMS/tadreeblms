<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Lesson;
use App\Models\LessonVideo;
use App\Models\Media;
use Tests\TestCase;

class MediaStreamTest extends TestCase
{
    private $createdFiles = [];

    protected function tearDown(): void
    {
        foreach ($this->createdFiles as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    /** @test */
    public function uploaded_media_streams_requested_byte_ranges()
    {
        config(['filesystems.default' => 'local']);

        $filename = 'media-range-test.mp4';
        $path = public_path('storage/uploads/' . $filename);
        $this->createStreamFile($path, '0123456789');

        $media = Media::create([
            'model_type' => Lesson::class,
            'model_id' => 1,
            'name' => $filename,
            'url' => asset('storage/uploads/' . $filename),
            'aws_url' => asset('storage/uploads/' . $filename),
            'type' => 'upload',
            'file_name' => $filename,
            'size' => 10,
        ]);

        $this->actingAs(factory(User::class)->create());

        $this->assertSame(route('media.stream', ['media' => $media->id]), $media->fresh()->url);

        $response = $this->withHeaders(['Range' => 'bytes=2-5'])
            ->get(route('media.stream', ['media' => $media->id]));

        $response->assertStatus(206);
        $response->assertHeader('Accept-Ranges', 'bytes');
        $response->assertHeader('Content-Range', 'bytes 2-5/10');
        $response->assertHeader('Content-Length', 4);
        $this->assertSame('2345', $this->streamedContent($response));
    }

    /** @test */
    public function lesson_video_uploads_stream_requested_byte_ranges()
    {
        config(['filesystems.default' => 'local']);

        $filename = 'lesson-video-range-test.mp4';
        $relativePath = 'lesson_videos/' . $filename;
        $path = storage_path('app/public/' . $relativePath);
        $this->createStreamFile($path, 'abcdefghij');

        $lesson = Lesson::create(['title' => 'Streaming lesson']);
        $lessonVideo = LessonVideo::create([
            'lesson_id' => $lesson->id,
            'title' => 'Range video',
            'type' => 'upload',
            'file_path' => $relativePath,
            'sort_order' => 0,
            'is_preview' => 0,
        ]);

        $this->actingAs(factory(User::class)->create());

        $this->assertSame(route('lesson-videos.stream', ['lessonVideo' => $lessonVideo->id]), $lessonVideo->playback_url);

        $response = $this->withHeaders(['Range' => 'bytes=3-6'])
            ->get(route('lesson-videos.stream', ['lessonVideo' => $lessonVideo->id]));

        $response->assertStatus(206);
        $response->assertHeader('Accept-Ranges', 'bytes');
        $response->assertHeader('Content-Range', 'bytes 3-6/10');
        $response->assertHeader('Content-Length', 4);
        $this->assertSame('defg', $this->streamedContent($response));
    }

    private function createStreamFile($path, $content)
    {
        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0777, true);
        }

        file_put_contents($path, $content);
        $this->createdFiles[] = $path;
    }

    private function streamedContent($response)
    {
        ob_start();
        $response->baseResponse->sendContent();

        return ob_get_clean();
    }
}
