<?php

namespace App\Services\Kpi;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class KpiMetricDataProvider
{
    public function supportsType(string $type): bool
    {
        return $this->resolveCalculatorMethod($type) !== null;
    }

    /**
     * @param string $type
     * @param array $courseIds
     * @return float
     */
    public function getMetricValueForType($type, array $courseIds = []): float
    {
        $scopedCourseIds = $this->resolveIncludedCourseIds($courseIds);
        // Empty after resolution means no included courses exist at all — return nothing.
        if (empty($scopedCourseIds)) {
            return 0.0;
        }

        $method = $this->resolveCalculatorMethod((string) $type);
        if ($method === null) {
            return 0.0;
        }

        return $this->{$method}($scopedCourseIds);
    }

    protected function resolveCalculatorMethod(string $type): ?string
    {
        $normalizedType = trim($type);
        if ($normalizedType === '') {
            return null;
        }

        $method = 'calculate' . Str::studly($normalizedType) . 'Value';

        return method_exists($this, $method) ? $method : null;
    }

    protected function calculateCompletionValue(array $courseIds)
    {
        if (
            Schema::hasTable('employee_course_progress')
            && Schema::hasColumn('employee_course_progress', 'progress')
            && Schema::hasColumn('employee_course_progress', 'course_id')
        ) {
            $avg = DB::table('employee_course_progress')
                ->whereNotNull('progress')
                ->whereIn('course_id', $courseIds)
                ->avg('progress');
            return $this->normalizePercent($avg);
        }

        if (
            Schema::hasTable('subscribe_courses')
            && Schema::hasColumn('subscribe_courses', 'progress_percent')
            && Schema::hasColumn('subscribe_courses', 'course_id')
        ) {
            $avg = DB::table('subscribe_courses')
                ->whereNotNull('progress_percent')
                ->whereIn('course_id', $courseIds)
                ->avg('progress_percent');
            return $this->normalizePercent($avg);
        }

        return 0.0;
    }

    protected function calculateScoreValue(array $courseIds)
    {
        if (
            Schema::hasTable('tests_results')
            && Schema::hasColumn('tests_results', 'test_result')
            && Schema::hasColumn('tests_results', 'test_id')
            && Schema::hasTable('tests')
            && Schema::hasColumn('tests', 'id')
            && Schema::hasColumn('tests', 'course_id')
        ) {
            $avg = DB::table('tests_results')
                ->join('tests', 'tests.id', '=', 'tests_results.test_id')
                ->whereNotNull('tests_results.test_result')
                ->whereIn('tests.course_id', $courseIds)
                ->avg('tests_results.test_result');
            return $this->normalizePercent($avg);
        }

        if (
            Schema::hasTable('subscribe_courses')
            && Schema::hasColumn('subscribe_courses', 'assignment_score')
            && Schema::hasColumn('subscribe_courses', 'course_id')
        ) {
            $avg = DB::table('subscribe_courses')
                ->whereNotNull('assignment_score')
                ->whereIn('course_id', $courseIds)
                ->avg('assignment_score');
            return $this->normalizePercent($avg);
        }

        return 0.0;
    }

    protected function calculateActivityValue(array $courseIds)
    {
        $videoScore = 0.0;
        $attendanceScore = 0.0;

        if (Schema::hasTable('video_progresses')) {
            $videoQuery = DB::table('video_progresses');

            if (Schema::hasColumn('video_progresses', 'course_id')) {
                $videoQuery->whereIn('video_progresses.course_id', $courseIds);
            } elseif (
                Schema::hasColumn('video_progresses', 'lesson_id')
                && Schema::hasTable('lessons')
                && Schema::hasColumn('lessons', 'id')
                && Schema::hasColumn('lessons', 'course_id')
            ) {
                $videoQuery->join('lessons', 'lessons.id', '=', 'video_progresses.lesson_id')
                    ->whereIn('lessons.course_id', $courseIds);
            } else {
                $videoQuery = null;
            }

            if ($videoQuery !== null) {
                if (Schema::hasColumn('video_progresses', 'progress')) {
                    $videoScore = $this->normalizePercent($videoQuery->whereNotNull('video_progresses.progress')->avg('video_progresses.progress'));
                } else {
                    $count = (int) $videoQuery->count();
                    $videoScore = min(100.0, $count * 2.0);
                }
            }
        }

        if (Schema::hasTable('live_session_attendances')) {
            $attendanceQuery = DB::table('live_session_attendances');

            if (Schema::hasColumn('live_session_attendances', 'course_id')) {
                $attendanceQuery->whereIn('live_session_attendances.course_id', $courseIds);
            } elseif (
                Schema::hasColumn('live_session_attendances', 'lesson_id')
                && Schema::hasTable('lessons')
                && Schema::hasColumn('lessons', 'id')
                && Schema::hasColumn('lessons', 'course_id')
            ) {
                $attendanceQuery->join('lessons', 'lessons.id', '=', 'live_session_attendances.lesson_id')
                    ->whereIn('lessons.course_id', $courseIds);
            } elseif (
                Schema::hasColumn('live_session_attendances', 'live_session_id')
                && Schema::hasTable('live_sessions')
                && Schema::hasColumn('live_sessions', 'id')
                && Schema::hasColumn('live_sessions', 'course_id')
            ) {
                $attendanceQuery->join('live_sessions', 'live_sessions.id', '=', 'live_session_attendances.live_session_id')
                    ->whereIn('live_sessions.course_id', $courseIds);
            } else {
                $attendanceQuery = null;
            }

            if ($attendanceQuery !== null) {
                $attendanceCount = (int) $attendanceQuery->count();
                $attendanceScore = min(100.0, $attendanceCount * 2.0);
            }
        }

        return round(($videoScore * 0.6) + ($attendanceScore * 0.4), 2);
    }

    protected function calculateTimeValue(array $courseIds)
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'duration')) {
            $query = DB::table('assignments')->whereNotNull('duration');

            if (Schema::hasColumn('assignments', 'course_id')) {
                $query->whereIn('assignments.course_id', $courseIds);
            } elseif (
                Schema::hasColumn('assignments', 'test_id')
                && Schema::hasTable('tests')
                && Schema::hasColumn('tests', 'id')
                && Schema::hasColumn('tests', 'course_id')
            ) {
                $query->join('tests', 'tests.id', '=', 'assignments.test_id')
                    ->whereIn('tests.course_id', $courseIds);
            } else {
                return 0.0;
            }

            $avgMinutes = $query->avg('assignments.duration');
            if ($avgMinutes === null) {
                return 0.0;
            }

            // 60 minutes = full score baseline for normalized time KPI.
            $normalized = ((float) $avgMinutes / 60) * 100;
            return round(min(100.0, max(0.0, $normalized)), 2);
        }

        return 0.0;
    }

    /**
     * @param array $courseIds
     * @return array
     */
    protected function resolveIncludedCourseIds(array $courseIds)
    {
        $courseIds = collect($courseIds)->map(function ($id) {
            return (int) $id;
        })->filter()->unique()->values()->toArray();

        if (!Schema::hasTable('courses') || !Schema::hasColumn('courses', 'id')) {
            return $courseIds;
        }

        $query = DB::table('courses');

        if (Schema::hasColumn('courses', 'deleted_at')) {
            $query->whereNull('deleted_at');
        }

        // Empty means the KPI applies to all courses (no specific scoping).
        if (!empty($courseIds)) {
            $query->whereIn('id', $courseIds);
        }

        if (Schema::hasColumn('courses', 'include_in_kpi')) {
            $query->where('include_in_kpi', true);
        }

        return $query->pluck('id')
            ->map(function ($id) {
                return (int) $id;
            })
            ->values()
            ->toArray();
    }

    /**
     * @param mixed $value
     * @return float
     */
    protected function normalizePercent($value)
    {
        if ($value === null) {
            return 0.0;
        }

        return round(min(100.0, max(0.0, (float) $value)), 2);
    }
}
