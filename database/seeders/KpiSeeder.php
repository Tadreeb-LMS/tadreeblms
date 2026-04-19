<?php

namespace Database\Seeders;

use App\Models\Auth\User;
use App\Models\Kpi;
use Illuminate\Database\Seeder;

class KpiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $seedUserId = User::query()->value('id');

        $kpis = [
            [
                'name' => 'Course Completion Rate',
                'code' => 'COURSE_COMPLETION_RATE',
                'type' => 'completion',
                'description' => 'Percentage of enrolled users who completed assigned courses within the reporting period.',
                'weight' => 30,
                'is_active' => true,
            ],
            [
                'name' => 'Assessment Pass Rate',
                'code' => 'ASSESSMENT_PASS_RATE',
                'type' => 'score',
                'description' => 'Percentage of users who achieved the minimum passing score in assessments.',
                'weight' => 25,
                'is_active' => true,
            ],
            [
                'name' => 'Average Assessment Score',
                'code' => 'AVG_ASSESSMENT_SCORE',
                'type' => 'score',
                'description' => 'Average score achieved by users across all completed assessments.',
                'weight' => 20,
                'is_active' => true,
            ],
            [
                'name' => 'Training Attendance Rate',
                'code' => 'TRAINING_ATTENDANCE_RATE',
                'type' => 'activity',
                'description' => 'Attendance percentage for scheduled training sessions and live classes.',
                'weight' => 10,
                'is_active' => false,
            ],
            [
                'name' => 'Assignment Submission Timeliness',
                'code' => 'ASSIGNMENT_SUBMISSION_TIMELINESS',
                'type' => 'time',
                'description' => 'Percentage of assignments submitted on or before due date.',
                'weight' => 15,
                'is_active' => true,
            ],
            [
                'name' => 'Learner Engagement Index',
                'code' => 'LEARNER_ENGAGEMENT_INDEX',
                'type' => 'activity',
                'description' => 'Composite KPI from logins, lesson progress, and assessment activity.',
                'weight' => 5,
                'is_active' => false,
            ],
        ];

        foreach ($kpis as $kpiData) {
            $kpi = Kpi::withTrashed()->firstOrNew(['code' => $kpiData['code']]);
            $kpi->fill(array_merge($kpiData, [
                'created_by' => $kpi->exists ? $kpi->created_by : $seedUserId,
                'updated_by' => $seedUserId,
            ]));
            $kpi->save();

            if ($kpi->trashed()) {
                $kpi->restore();
            }
        }
    }
}
