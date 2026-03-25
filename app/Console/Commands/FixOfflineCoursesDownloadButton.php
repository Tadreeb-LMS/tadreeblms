<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Stripe\SubscribeCourse;
use App\Helpers\CustomHelper;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use App\Models\CourseFeedback;
use App\Models\UserFeedback;
use Illuminate\Support\Facades\Log;

class FixOfflineCoursesDownloadButton extends Command
{
    protected $signature = 'offline-courses-download-btn-fix';
    protected $description = 'Fixes offline course progress and download button issues';

    public function handle()
    {
        Log::info("=== Offline Course Fix Started ===");

        SubscribeCourse::query()
            ->with('course')
            //->where('grant_certificate', '!=', '1')
            //->where('is_completed', '!=', '1')
            //->where('course_id', '426')
            //->where('id', '12151')
            ->orderBy('id', 'DESC')
            ->chunkById(200, function ($rows) {

                foreach ($rows as $row) {

                    if (!$row->course) {
                        Log::warning("Course missing for subscription ID {$row->id}");
                        continue;
                    }

                    try {

                        //dd($row->course->is_online);

                        if ($row->course->is_online == 'Offline' || $row->course->is_online == 'Live-Classroom') {
                        //if(0) {
                            $has_assesment = false;
                            $course_id = $row->course_id;
                            $logged_in_user_id = $row->user_id;
                            // Update progress via your helper
                            // $progress = CustomHelper::updateUserProgress(
                            //     $row->user_id,
                            //     $row->course->id
                            // );
                            // $row->assignment_progress = $progress;

                            $has_feedback = CourseFeedback::query()
                                ->where('course_id', $course_id)
                                ->count() ?? 0;



                            //dd($has_feedback);

                            $feedback_given = UserFeedback::query()
                                ->where('user_id', $logged_in_user_id)
                                ->where('course_id', $course_id)
                                ->count() ?? 0;


                            $agmt = Assignment::where('assignments.course_id', $course_id)
                                ->join('courses', 'courses.id', '=', 'assignments.course_id')
                                ->join('course_assignment', 'course_assignment.course_id', '=', 'courses.id')
                                ->join('tests', 'tests.id', '=', 'assignments.test_id')
                                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                                //->whereRaw('FIND_IN_SET(?, `assign_to`) > 0', $logged_in_user_id)
                                ->exists();
                            if ($agmt) {
                                $has_assesment = true;
                            }

                            $agmt = Assignment::where('assignments.course_id', $course_id)
                                ->join('courses', 'courses.id', '=', 'assignments.course_id')
                                ->join('course_assignment', 'course_assignment.course_id', '=', 'courses.id')
                                ->join('tests', 'tests.id', '=', 'assignments.test_id')
                                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                                ->whereRaw('FIND_IN_SET(?, `assign_to`) > 0', $logged_in_user_id)
                                ->exists();
                            $assesment_status = null;
                            if ($agmt) {
                                $has_assesment = true;

                                $has_any_assesment_given = AssignmentQuestion::query()
                                    ->join('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')
                                    ->where('assessment_account_id', $logged_in_user_id)
                                    ->groupBy('question_id')
                                    ->count();

                                //dd($has_any_assesment_given);

                                $score = @$row->assignmentRawScore($logged_in_user_id) ?? 0;
                                if ($score >= 70) {
                                    $assesment_status = 'Passed';
                                } else {
                                    $assesment_status = 'Failed';
                                }
                            }

                            //($feedback_given, $has_feedback);

                            $row->has_assesment = $has_assesment ? 1 : 0;
                            //$row->assesment_taken = $has_assesment ? 1 : 0;

                            $row->has_feedback = $has_feedback > 0 ? 1 : 0;


                            if ($row->course->is_online == 'Offline' || $row->course->is_online == 'Live-Classroom') {
                                if ($row->is_attended == 1) {
                                    $row->assignment_status = $has_assesment ? $assesment_status : 'Not Applied';
                                    $row->feedback_given = $feedback_given;
                                } else {
                                    if ($has_assesment) {
                                        $row->assignment_status = 'Not Started';
                                    } else {
                                        $row->assignment_status = 'Not Applied';
                                    }
                                }
                            }

                            $row->save();

                            $helper = app(CustomHelper::class);
                            $progress = $helper->getCourseProgress($row->course,  $row, $row->user_id);

                            if ($row->course->is_online == 'Offline' || $row->course->is_online == 'Live-Classroom') {
                                if ($row->is_attended == 1) {
                                    $row->assignment_progress = $progress;
                                } else {
                                    $row->assignment_progress = 0;
                                }
                            }
                            $row->save();

                            if ($row->is_attended == 1) {
                                CustomHelper::updateGrantCertificate($row->course_id, $row->user_id);
                            } else {
                                // Offline/Live-Classroom courses must not remain completed if attendance is missing.
                                if ($row->grant_certificate == 1 || $row->is_completed == 1 || !empty($row->completed_at)) {
                                    $row->grant_certificate = 0;
                                    $row->is_completed = 0;
                                    $row->completed_at = null;
                                    $row->save();
                                }
                            }
                        }

                    } catch (\Exception $e) {
                        Log::error("Fix failed for user {$row->user_id}, course {$row->course->id}: " . $e->getMessage());
                    }
                }
            });

        Log::info("=== Offline Course Fix Completed ===");

        return 0;
    }
}
