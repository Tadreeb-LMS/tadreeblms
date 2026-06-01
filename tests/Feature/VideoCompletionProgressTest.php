<?php

namespace Tests\Feature;

use App\Models\Auth\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Stripe\SubscribeCourse;
use App\Models\VideoProgress;
use Tests\TestCase;

class VideoCompletionProgressTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.key' => 'base64:' . base64_encode(str_repeat('a', 32))]);

        $this->withoutMiddleware([
            \App\Http\Middleware\RedirectIfNotInstalled::class,
            \App\Http\Middleware\CheckInstallation::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        $pdo = $this->app['db']->connection()->getPdo();
        if (method_exists($pdo, 'sqliteCreateFunction')) {
            $pdo->sqliteCreateFunction('FIND_IN_SET', function ($needle, $haystack) {
                if ($needle === null || $haystack === null) {
                    return 0;
                }

                $values = array_map('trim', explode(',', (string) $haystack));
                $index = array_search((string) $needle, $values, true);

                return $index === false ? 0 : $index + 1;
            }, 2);
        }
    }

    public function test_video_completion_marks_lesson_complete_and_updates_course_progress()
    {
        $user = factory(User::class)->create();
        $course = $this->createCourse();
        $lesson = $this->createLesson($course);
        $this->createLesson($course, ['title' => 'Second lesson', 'slug' => 'second-lesson']);
        $media = $this->createVideo($lesson);

        SubscribeCourse::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 1,
            'assignment_progress' => 0,
            'has_assesment' => 0,
            'has_feedback' => 0,
        ]);

        $response = $this->actingAs($user)->postJson(route('video.progress.update'), [
            'vedio_id' => $media->id,
            'watchPoint' => 100,
            'duration' => 120,
            'progress' => 120,
            'completed' => 1,
        ]);

        $response->assertOk()
            ->assertJson([
                'progress_per' => 100,
                'lesson_completed' => true,
            ]);

        $this->assertDatabaseHas('video_progresses', [
            'media_id' => $media->id,
            'user_id' => $user->id,
            'complete' => 1,
        ]);

        $this->assertDatabaseHas('chapter_students', [
            'model_type' => Lesson::class,
            'model_id' => $lesson->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        $this->assertDatabaseHas('attendance_student', [
            'student_id' => $user->id,
            'course_id' => $course->id,
            'lesson_id' => $lesson->id,
        ]);

        $this->assertDatabaseHas('subscribe_courses', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'assignment_progress' => 50,
        ]);
    }

    public function test_video_completion_recalculates_course_progress_for_existing_lesson_completion()
    {
        $user = factory(User::class)->create();
        $course = $this->createCourse();
        $lesson = $this->createLesson($course);
        $media = $this->createVideo($lesson);

        SubscribeCourse::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'status' => 1,
            'assignment_progress' => 0,
            'has_assesment' => 0,
            'has_feedback' => 0,
        ]);

        $lesson->chapterStudents()->create([
            'model_type' => Lesson::class,
            'model_id' => $lesson->id,
            'user_id' => $user->id,
            'course_id' => $course->id,
        ]);

        VideoProgress::unguarded(function () use ($media, $user) {
            VideoProgress::create([
                'media_id' => $media->id,
                'user_id' => $user->id,
                'duration' => 120,
                'progress' => 110,
                'progress_per' => 92,
                'complete' => 0,
            ]);
        });

        $response = $this->actingAs($user)->postJson(route('video.progress.update'), [
            'vedio_id' => $media->id,
            'watchPoint' => 100,
            'duration' => 120,
            'progress' => 120,
            'completed' => 1,
        ]);

        $response->assertOk()
            ->assertJson([
                'progress_per' => 100,
                'lesson_completed' => true,
            ]);

        $this->assertDatabaseHas('subscribe_courses', [
            'user_id' => $user->id,
            'course_id' => $course->id,
            'assignment_progress' => 100,
            'course_progress_status' => 2,
            'is_completed' => 1,
        ]);
    }

    private function createCourse(array $attributes = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Video progress course',
            'slug' => 'video-progress-course',
            'category_id' => 1,
            'description' => 'Course used for video progress tests.',
            'published' => 1,
            'is_online' => 'Online',
        ], $attributes));
    }

    private function createLesson(Course $course, array $attributes = []): Lesson
    {
        return Lesson::create(array_merge([
            'course_id' => $course->id,
            'title' => 'Video lesson',
            'slug' => 'video-lesson',
            'position' => 1,
            'free_lesson' => 1,
            'published' => 1,
            'full_text' => 'Lesson content.',
        ], $attributes));
    }

    private function createVideo(Lesson $lesson): Media
    {
        return Media::create([
            'model_type' => Lesson::class,
            'model_id' => $lesson->id,
            'name' => 'lesson-video.mp4',
            'url' => 'lesson-video.mp4',
            'type' => 'upload',
            'file_name' => 'lesson-video.mp4',
            'size' => 120,
        ]);
    }
}
