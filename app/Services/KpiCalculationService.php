<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class KpiCalculationService
{
    protected $typeValueCache = [];

    public function getSupportedTypeKeys()
    {
        return array_keys(config('kpi.types', []));
    }

    public function calculateForKpi($kpi, $totalActiveWeight)
    {
        if (!$kpi->is_active) {
            return [
                'excluded' => true,
                'value' => null,
                'weighted_score' => null,
            ];
        }

        $value = $this->calculateTypeValue($kpi->type);
        $weight = (float) $kpi->weight;
        $totalWeight = (float) $totalActiveWeight;

        $weightedScore = 0.0;
        if ($totalWeight > 0 && $weight >= 0) {
            $weightedScore = ($value * $weight) / $totalWeight;
        }

        return [
            'excluded' => false,
            'value' => round($value, 2),
            'weighted_score' => round($weightedScore, 2),
        ];
    }

    public function calculateTypeValue($type)
    {
        if (array_key_exists($type, $this->typeValueCache)) {
            return $this->typeValueCache[$type];
        }

        $value = 0.0;
        switch ($type) {
            case 'completion':
                $value = $this->calculateCompletionValue();
                break;
            case 'score':
                $value = $this->calculateScoreValue();
                break;
            case 'activity':
                $value = $this->calculateActivityValue();
                break;
            case 'time':
                $value = $this->calculateTimeValue();
                break;
            default:
                $value = 0.0;
        }

        $this->typeValueCache[$type] = $value;

        return $value;
    }

    protected function calculateCompletionValue()
    {
        if (Schema::hasTable('employee_course_progress') && Schema::hasColumn('employee_course_progress', 'progress')) {
            $avg = DB::table('employee_course_progress')->whereNotNull('progress')->avg('progress');
            return $this->normalizePercent($avg);
        }

        if (Schema::hasTable('subscribe_courses') && Schema::hasColumn('subscribe_courses', 'progress_percent')) {
            $avg = DB::table('subscribe_courses')->whereNotNull('progress_percent')->avg('progress_percent');
            return $this->normalizePercent($avg);
        }

        return 0.0;
    }

    protected function calculateScoreValue()
    {
        if (Schema::hasTable('tests_results') && Schema::hasColumn('tests_results', 'test_result')) {
            $avg = DB::table('tests_results')->whereNotNull('test_result')->avg('test_result');
            return $this->normalizePercent($avg);
        }

        if (Schema::hasTable('subscribe_courses') && Schema::hasColumn('subscribe_courses', 'assignment_score')) {
            $avg = DB::table('subscribe_courses')->whereNotNull('assignment_score')->avg('assignment_score');
            return $this->normalizePercent($avg);
        }

        return 0.0;
    }

    protected function calculateActivityValue()
    {
        $videoScore = 0.0;
        $attendanceScore = 0.0;

        if (Schema::hasTable('video_progresses')) {
            if (Schema::hasColumn('video_progresses', 'progress')) {
                $videoScore = $this->normalizePercent(DB::table('video_progresses')->whereNotNull('progress')->avg('progress'));
            } else {
                $count = (int) DB::table('video_progresses')->count();
                $videoScore = min(100.0, $count * 2.0);
            }
        }

        if (Schema::hasTable('live_session_attendances')) {
            $attendanceCount = (int) DB::table('live_session_attendances')->count();
            $attendanceScore = min(100.0, $attendanceCount * 2.0);
        }

        return round(($videoScore * 0.6) + ($attendanceScore * 0.4), 2);
    }

    protected function calculateTimeValue()
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'duration')) {
            $avgMinutes = DB::table('assignments')->whereNotNull('duration')->avg('duration');
            if ($avgMinutes === null) {
                return 0.0;
            }

            // 60 minutes = full score baseline for normalized time KPI.
            $normalized = ((float) $avgMinutes / 60) * 100;
            return round(min(100.0, max(0.0, $normalized)), 2);
        }

        return 0.0;
    }

    protected function normalizePercent($value)
    {
        if ($value === null) {
            return 0.0;
        }

        return round(min(100.0, max(0.0, (float) $value)), 2);
    }
}
