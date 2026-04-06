<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Models\Course;
use App\Models\Lesson;
use App\Models\Stripe\SubscribeCourse;
use App\Services\LmsEventRecorder;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Seeds a self-contained test environment for the LMS KPI event capture
 * pipeline (issue #415).
 *
 * Safe to run multiple times — all inserts use firstOrCreate / updateOrInsert
 * so re-running simply refreshes data without duplicating rows.
 *
 * What it creates
 * ───────────────
 * • 1 published course  ("KPI Live Test Course", slug kpi-live-test-course)
 * • 2 published lessons attached to that course
 * • 1 course-level quiz (test) with 2 MCQ questions
 * • student@lms.com enrolled in the course (subscribe_courses)
 * • Both lessons marked complete for the student (chapter_students)
 * • A quiz attempt at 80 % score (tests_results)
 * • The course marked as completed via Eloquent → triggers
 *   SubscribeCourseObserver → fires course_completed event
 * • 5 back-dated lms_kpi_events (2 logins + 1 quiz_attempt + 2
 *   course_completed) directly inserted so the KPI dashboard has
 *   historical data to aggregate
 *
 * Users used (created by AuthTableSeeder / UserTableSeeder)
 * ─────────────────────────────────────────────────────────
 * student@lms.com  (id 3)  — learner
 * teacher@lms.com  (id 2)  — course teacher
 * admin@seeder.com (id 1)  — created_by reference
 */
class LmsEventCaptureSeeder extends Seeder
{
    public function run(): void
    {
        $student = User::where('email', 'student@lms.com')->firstOrFail();
        $teacher = User::where('email', 'teacher@lms.com')->first()
            ?? User::where('email', 'admin@seeder.com')->firstOrFail();
        $categoryId = DB::table('categories')->value('id')
            ?? $this->createCategory();

        // ── 1. Course ────────────────────────────────────────────────────────
        $course = Course::withoutGlobalScopes()->firstOrCreate(
            ['slug' => 'kpi-live-test-course'],
            [
                'category_id' => $categoryId,
                'title'       => 'KPI Live Test Course',
                'description' => 'Seeded course for live-testing the LMS KPI event capture pipeline.',
                'price'       => 0,
                'published'   => 1,
                'is_online'   => 'Online',
            ]
        );

        // Link teacher to course
        DB::table('course_user')->updateOrInsert(
            ['course_id' => $course->id, 'user_id' => $teacher->id]
        );

        // ── 2. Lessons ───────────────────────────────────────────────────────
        $lesson1 = Lesson::firstOrCreate(
            ['slug' => 'kpi-lesson-introduction'],
            [
                'course_id'  => $course->id,
                'title'      => 'Introduction to KPI Tracking',
                'short_text' => 'Overview of KPI concepts.',
                'published'  => 1,
                'position'   => 1,
            ]
        );

        $lesson2 = Lesson::firstOrCreate(
            ['slug' => 'kpi-lesson-module1'],
            [
                'course_id'  => $course->id,
                'title'      => 'Module 1: Capturing Events',
                'short_text' => 'How the event capture service works.',
                'published'  => 1,
                'position'   => 2,
            ]
        );

        // ── 3. Quiz (course-level test) ──────────────────────────────────────
        $test = DB::table('tests')->where('slug', 'kpi-live-test-quiz')->first();
        if (! $test) {
            $testId = DB::table('tests')->insertGetId([
                'course_id'     => $course->id,
                'lesson_id'     => null,
                'title'         => 'KPI Course Quiz',
                'description'   => 'Auto-seeded quiz for event capture testing.',
                'slug'          => 'kpi-live-test-quiz',
                'passing_score' => 60,
                'published'     => 1,
                'created_at'    => Carbon::now(),
                'updated_at'    => Carbon::now(),
            ]);

            // Two MCQ questions
            $q1Options = json_encode([
                ['option' => 'LmsEventRecorder', 'is_correct' => true],
                ['option' => 'CourseController',  'is_correct' => false],
                ['option' => 'AuthMiddleware',    'is_correct' => false],
            ]);
            $q2Options = json_encode([
                ['option' => 'lms_kpi_events',  'is_correct' => true],
                ['option' => 'subscribe_courses', 'is_correct' => false],
                ['option' => 'tests_results',    'is_correct' => false],
            ]);

            DB::table('test_questions')->insert([
                [
                    'test_id'       => $testId,
                    'question_type' => 'mcq',
                    'question_text' => 'Which service writes events to the KPI event log?',
                    'option_json'   => $q1Options,
                    'marks'         => 50,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
                [
                    'test_id'       => $testId,
                    'question_type' => 'mcq',
                    'question_text' => 'Which table stores structured LMS KPI events?',
                    'option_json'   => $q2Options,
                    'marks'         => 50,
                    'created_at'    => Carbon::now(),
                    'updated_at'    => Carbon::now(),
                ],
            ]);
        } else {
            $testId = $test->id;
        }

        // ── 4. Enrol student ─────────────────────────────────────────────────
        // firstOrCreate so we never reset is_completed on re-runs
        SubscribeCourse::firstOrCreate(
            ['user_id' => $student->id, 'course_id' => $course->id],
            [
                'status'           => 1,
                'assign_date'      => Carbon::now()->subDays(7)->toDateString(),
                'is_completed'     => 0,
                'has_assesment'    => 0,
                'has_feedback'     => 0,
            ]
        );

        // ── 5. Mark both lessons complete (chapter_students) ─────────────────
        foreach ([$lesson1->id, $lesson2->id] as $lessonId) {
            DB::table('chapter_students')->updateOrInsert(
                [
                    'model_type' => 'App\Models\Lesson',
                    'model_id'   => $lessonId,
                    'user_id'    => $student->id,
                    'course_id'  => $course->id,
                ],
                [
                    'created_at' => Carbon::now()->subDays(2),
                    'updated_at' => Carbon::now()->subDays(2),
                ]
            );
        }

        // ── 6. Quiz attempt — triggers recorder hook in LessonsController ────
        //    We insert the result directly here (simulating what the controller
        //    does) and call the recorder explicitly so the event appears.
        $existingResult = DB::table('tests_results')
            ->where('test_id', $testId)
            ->where('user_id', $student->id)
            ->first();

        if (! $existingResult) {
            DB::table('tests_results')->insert([
                'test_id'     => $testId,
                'user_id'     => $student->id,
                'test_result' => 80,
                'created_at'  => Carbon::now()->subDay(),
                'updated_at'  => Carbon::now()->subDay(),
            ]);

            app(LmsEventRecorder::class)->record(
                $student->id,
                LmsEventRecorder::TYPE_QUIZ_ATTEMPT,
                [
                    'course_id'      => $course->id,
                    'test_id'        => $testId,
                    'attempt_scope'  => 'course_test',
                    'score'          => 80,
                    'passed'         => true,
                    'source'         => 'seeder',
                ],
                Carbon::now()->subDay()
            );
        }

        // ── 7. Complete the course via Eloquent → observer fires automatically
        $subscription = SubscribeCourse::where('user_id', $student->id)
            ->where('course_id', $course->id)
            ->first();

        if ($subscription && ! $subscription->is_completed) {
            $subscription->is_completed = 1;
            $subscription->completed_at = Carbon::now();
            $subscription->course_progress_status = 2;
            $subscription->save();
            // SubscribeCourseObserver::updated() fires here and records the event
        }

        // ── 8. Back-dated historical events ──────────────────────────────────
        //    Insert directly (bypass recorder) to simulate weeks of production
        //    history for the KPI aggregation queries to work with.
        //    Guard: skip if any seeder-origin event already exists (idempotent).
        $alreadySeeded = DB::table('lms_kpi_events')
            ->where('user_id', $student->id)
            ->whereRaw("JSON_UNQUOTE(JSON_EXTRACT(payload, '$.source')) IN ('seeder_history','seeder_login')")
            ->exists();

        if (! $alreadySeeded) {
            $historical = [
                // Two logins before the course was started
                [
                    'user_id'    => $student->id,
                    'event_type' => LmsEventRecorder::TYPE_USER_LOGIN,
                    'occurred_at'=> Carbon::now()->subDays(10),
                    'payload'    => json_encode(['source' => 'seeder_login', 'ip' => '127.0.0.1']),
                    'created_at' => Carbon::now()->subDays(10),
                ],
                [
                    'user_id'    => $student->id,
                    'event_type' => LmsEventRecorder::TYPE_USER_LOGIN,
                    'occurred_at'=> Carbon::now()->subDays(5),
                    'payload'    => json_encode(['source' => 'seeder_login', 'ip' => '127.0.0.1']),
                    'created_at' => Carbon::now()->subDays(5),
                ],
                // An earlier failed quiz attempt
                [
                    'user_id'    => $student->id,
                    'event_type' => LmsEventRecorder::TYPE_QUIZ_ATTEMPT,
                    'occurred_at'=> Carbon::now()->subDays(3),
                    'payload'    => json_encode([
                        'course_id'     => $course->id,
                        'test_id'       => $testId,
                        'attempt_scope' => 'course_test',
                        'score'         => 45,
                        'passed'        => false,
                        'source'        => 'seeder_history',
                    ]),
                    'created_at' => Carbon::now()->subDays(3),
                ],
            ];

            DB::table('lms_kpi_events')->insert($historical);
        }

        $this->command->info('LmsEventCaptureSeeder done.');
        $this->command->table(
            ['Event', 'Count'],
            DB::table('lms_kpi_events')
                ->selectRaw('event_type, count(*) as cnt')
                ->groupBy('event_type')
                ->get()
                ->map(fn ($r) => [$r->event_type, $r->cnt])
                ->toArray()
        );
    }

    private function createCategory(): int
    {
        return DB::table('categories')->insertGetId([
            'name'       => 'KPI Testing',
            'slug'       => 'kpi-testing',
            'status'     => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ]);
    }
}
