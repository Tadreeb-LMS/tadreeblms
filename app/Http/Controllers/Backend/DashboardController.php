<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\Auth\User;
use App\Models\Bundle;
use App\Models\Contact;
use App\Models\Course;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Review;
use App\Helpers\CustomHelper;
use App\Models\Assignment;
use App\Models\Stripe\SubscribeCourse;
use App\Models\UserLearningPathway;
use Session;
use Illuminate\Database\Eloquent\Collection;
use App\Models\LearningPathwayAssignment;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use App\Models\ChapterStudent;
use App\Models\courseAssignment;
use App\Models\AssignmentQuestion;
use App\Models\Category;
use App\Models\Department;
use App\Models\EmployeeProfile;
use App\Models\TestQuestionOption;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Class DashboardController.
 */
class DashboardController extends Controller
{


    public function buildDashboardCache(int $cache_duration = 30, $user = null)
    {
        $userId = $user ? $user->id : (auth()->id() ?? 'system');
        $cacheKey = "{$userId}_dashboard_stats";
        $cachePath = storage_path('framework/cache/data');

        // ❌ If cache directory broken → skip caching entirely
        if (!is_dir($cachePath) || !is_writable($cachePath)) {
            return $this->dashboardQuery($user); // normal query
        }

        // ✅ Safe: only run Cache::remember when directory is valid
        return Cache::remember($cacheKey, now()->addMinutes($cache_duration), function () use ($user) {
            return $this->dashboardQuery($user);
        });
    }


    public function dashboardQuery($user = null)
    {
        $teacherId = null;
        if ($user && $user->hasRole('teacher')) {
            $teacherId = $user->id;
        }

        // --- Basic collections ---
        $internal_users     = User::select('id', 'first_name', 'email', 'employee_type')
            ->where('employee_type', 'internal')
            ->get();

        $published_courses  = Course::select('id', 'title', 'published')
            ->get();
        $departments        = Department::select('id', 'title')->where('published', 1)->get();
        $categories         = Category::select('id', 'name')->where('status', 1)->get();

        // --- Aggregate counts ---
        $students_count = User::role('student')
            ->distinct('users.email')
            ->where('users.employee_type', 'internal')
            ->where('users.active', 1)
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->join('subscribe_courses', 'users.id', '=', 'subscribe_courses.user_id')
                    ->join('courses', 'subscribe_courses.course_id', '=', 'courses.id')
                    ->join('course_user', 'courses.id', '=', 'course_user.course_id')
                    ->where('course_user.user_id', $teacherId);
            })
            ->count();

        $teachers_count = User::role('teacher')->count();
        $courses_count  = Course::when($teacherId, function ($query) use ($teacherId) {
            $query->whereHas('teachers', function ($q) use ($teacherId) {
                $q->where('course_user.user_id', $teacherId);
            });
        })->count() + Bundle::when($teacherId, function ($query) use ($teacherId) {
            $query->where('user_id', $teacherId);
        })->count();

        // --- Recents ---
        $recent_orders = Order::latest()->take(10)
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->whereExists(function ($q) use ($teacherId) {
                    $q->select(\DB::raw(1))
                        ->from('order_items')
                        ->join('course_user', 'order_items.item_id', '=', 'course_user.course_id')
                        ->whereColumn('order_items.order_id', 'orders.id')
                        ->where('course_user.user_id', $teacherId)
                        ->where('order_items.item_type', 'like', '%Course%');
                });
            })
            ->get(['id', 'created_at']);
        $recent_subscriptions = SubscribeCourse::latest()->take(10)
            ->get(['id', 'course_id', 'user_id', 'created_at']);
        $recent_contacts      = Contact::latest()->take(10)->get(['id', 'name', 'email', 'created_at']);

        // --- Assignment & Certificate stats ---
        $total_assignments = DB::table('assignments')
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->join('courses', 'assignments.course_id', '=', 'courses.id')
                    ->join('course_user', 'courses.id', '=', 'course_user.course_id')
                    ->where('course_user.user_id', $teacherId);
            })
            ->whereNull('assignments.deleted_at')
            ->count();

        $total_certificate_issued = DB::table('certificates as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->join('subscribe_courses as sc', 's.user_id', '=', 'sc.user_id')
                    ->join('courses as c', 'sc.course_id', '=', 'c.id')
                    ->join('course_user as cu', 'sc.course_id', '=', 'cu.course_id')
                    ->where('cu.user_id', $teacherId);
            })
            ->whereNull('u.deleted_at')
            ->where('u.active', 1)
            ->count('s.id');

        // --- Course completion ---
        $course_completion_data = DB::table('subscribe_courses as s')
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->join('courses as c', 's.course_id', '=', 'c.id')
                    ->join('course_user as cu', 'c.id', '=', 'cu.course_id')
                    ->where('cu.user_id', $teacherId);
            })
            ->whereNull('s.deleted_at')
            ->selectRaw('
                COUNT(CASE WHEN s.is_completed = 1 THEN 1 END) as completed_count,
                COUNT(CASE WHEN s.is_completed = 0 THEN 1 END) as not_completed_count
            ')
            ->first();

        $completedCount = $course_completion_data->completed_count ?? 0;
        $notCompletedCount = $course_completion_data->not_completed_count ?? 0;
        $total_rows = $completedCount + $notCompletedCount;

        $av_completion_rate = $total_rows > 0
            ? round(($completedCount / $total_rows) * 100)
            : 0;

        // --- Assessment performance ---
        $scoreData = SubscribeCourse::whereHas('course.courseAssignments')
            ->select('assesment_taken', 'assignment_score')
            ->get();

        $totalScore = 0;
        $totalAssessmentsTaken = 0;
        $totalAssessments = $scoreData->count();

        foreach ($scoreData as $subscription) {
            if ($subscription->assesment_taken) {
                $totalAssessmentsTaken++;
                $score = (float) rtrim($subscription->assignment_score, '%');
                $totalScore += $score;
            }
        }

        $completed_assessments = $totalAssessmentsTaken;
        $not_completed_assessments = $totalAssessments - $totalAssessmentsTaken;
        $av_completed_score = $totalAssessmentsTaken > 0
            ? round($totalScore / $totalAssessmentsTaken)
            : 0;

        // --- Assigned users ---
        $assigned_users_count = DB::table('subscribe_courses as s')
            ->join('users as u', 's.user_id', '=', 'u.id')
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->join('courses as c', 's.course_id', '=', 'c.id')
                    ->join('course_user as cu', 'c.id', '=', 'cu.course_id')
                    ->where('cu.user_id', $teacherId);
            })
            ->whereNull('s.deleted_at')
            ->where('u.active', 1)
            ->where('u.employee_type', 'internal')
            ->whereNull('u.deleted_at')
            ->distinct()
            ->count('s.user_id');

        return [
            'internal_users' => $internal_users,
            'published_courses' => $published_courses,
            'departments' => $departments,
            'categories' => $categories,
            'students_count' => $students_count,
            'teachers_count' => $teachers_count,
            'courses_count' => $courses_count,
            'recent_orders' => $recent_orders,
            'recent_subscriptions' => $recent_subscriptions,
            'recent_contacts' => $recent_contacts,
            'total_assignments' => $total_assignments,
            'total_certificate_issued' => $total_certificate_issued,
            'assigned_users_count' => $assigned_users_count,
            'course_completion' => [
                'completed' => $completedCount,
                'not_completed' => $notCompletedCount,
                'avg_completion_rate' => $av_completion_rate,
            ],
            'assessment_stats' => [
                'completed' => $completed_assessments,
                'not_completed' => $not_completed_assessments,
                'avg_score' => $av_completed_score,
            ],
        ];
    }


    public function buildDashboardCache_(int $cache_duration = 30, $user = null)
    {
        // Use authenticated admin ID if available (optional per-admin cache)
        $userId = $user ? $user->id : (auth()->id() ?? 'system');
        $teacherId = null;
        if ($user && $user->hasRole('teacher')) {
            $teacherId = $user->id;
        }

        $cacheKey = "{$userId}_dashboard_stats";

        $adminStats = Cache::remember($cacheKey, now()->addMinutes($cache_duration), function () use ($user, $teacherId) {
            // --- Basic collections ---
            $internal_users     = User::select('id', 'first_name', 'email', 'employee_type')
                ->where('employee_type', 'internal')
                ->get();

            $published_courses  = Course::select('id', 'title', 'published')
                ->get();
            $departments        = Department::select('id', 'title')
                ->where('published', 1)
                ->get();
            $categories         = Category::select('id', 'name')
                ->where('status', 1)
                ->get();

            // --- Aggregate counts ---
            $students_count = User::role('student')
                ->distinct('users.email')
                ->where('users.employee_type', 'internal')
                ->where('users.active', 1)
            ->when($teacherId, function ($query) use ($teacherId) {
                $query->join('subscribe_courses', 'users.id', '=', 'subscribe_courses.user_id')
                    ->join('courses', 'subscribe_courses.course_id', '=', 'courses.id')
                    ->join('course_user', 'courses.id', '=', 'course_user.course_id')
                    ->where('course_user.user_id', $teacherId);
            })
                ->count();

            $teachers_count = User::role('teacher')->count();
            $courses_count  = Course::when($teacherId, function ($query) use ($teacherId) {
                $query->whereHas('teachers', function ($q) use ($teacherId) {
                    $q->where('course_user.user_id', $teacherId);
                });
            })->count() + Bundle::when($teacherId, function ($query) use ($teacherId) {
                $query->where('user_id', $teacherId);
            })->count();

            // --- Recents ---
                    $recent_orders        = Order::latest()->take(10)
                        ->get(['id', 'created_at']);            $recent_subscriptions = SubscribeCourse::latest()->take(10)
                ->get(['id', 'course_id', 'user_id', 'created_at']);
            $recent_contacts      = Contact::latest()->take(10)->get(['id', 'name', 'email', 'created_at']);

            // --- Assignment & Certificate stats ---
            $total_assignments = DB::table('assignments')
                ->when($teacherId, function ($query) use ($teacherId) {
                    $query->join('courses', 'assignments.course_id', '=', 'courses.id')
                        ->where('courses.user_id', $teacherId);
                })
                ->whereNull('assignments.deleted_at')
                ->count('id');

            $total_certificate_issued = DB::table('certificates as s')
                ->join('users as u', 's.user_id', '=', 'u.id')
                            ->when($teacherId, function ($query) use ($teacherId) {
                                $query->join('subscribe_courses as sc', 's.user_id', '=', 'sc.user_id')
                                    ->join('courses as c', 'sc.course_id', '=', 'c.id')
                                    ->join('course_user as cu', 'c.id', '=', 'cu.course_id')
                                    ->where('cu.user_id', $teacherId);
                            })                ->whereNull('u.deleted_at')
                ->where('u.active', 1)
                ->count('s.id');

            // --- Course Completion stats ---
            $course_completion_data = DB::table('subscribe_courses as s')
                ->when($teacherId, function ($query) use ($teacherId) {
                    $query->join('courses as c', 's.course_id', '=', 'c.id')
                        ->where('c.user_id', $teacherId);
                })
                ->whereNull('s.deleted_at')
                ->selectRaw('
                    COUNT(CASE WHEN s.is_completed = 1 THEN 1 END) as completed_count,
                    COUNT(CASE WHEN s.is_completed = 0 THEN 1 END) as not_completed_count
                ')
                ->first();

            $completedCount    = $course_completion_data->completed_count ?? 0;
            $notCompletedCount = $course_completion_data->not_completed_count ?? 0;
            $total_rows        = $completedCount + $notCompletedCount;

            $av_completion_rate = $total_rows > 0
                ? round(($completedCount / $total_rows) * 100, 0)
                : 0;

            // --- Assignment Performance ---
            $scoreData = SubscribeCourse::whereHas('course.courseAssignments')
                ->select('assesment_taken', 'assignment_score')
                ->get();

            $totalScore            = 0;
            $totalAssessmentsTaken = 0;
            $totalAssessments      = $scoreData->count();

            foreach ($scoreData as $subscription) {
                if ($subscription->assesment_taken) {
                    $totalAssessmentsTaken++;
                    // Safely extract integer score
                    $score = (float) rtrim($subscription->assignment_score, '%');
                    $totalScore += $score;
                }
            }

            $completed_assessments     = $totalAssessmentsTaken;
            $not_completed_assessments = $totalAssessments - $totalAssessmentsTaken;
            $av_completed_score        = $totalAssessmentsTaken > 0
                ? round($totalScore / $totalAssessmentsTaken, 0)
                : 0;
            //$av_completed_score = 45;

            // --- Assigned Users ---
            $assigned_users_count = DB::table('subscribe_courses as s')
                ->join('users as u', 's.user_id', '=', 'u.id')
                ->when($teacherId, function ($query) use ($teacherId) {
                    $query->join('courses as c', 's.course_id', '=', 'c.id')
                        ->where('c.user_id', $teacherId);
                })
                ->whereNull('s.deleted_at')
                ->where('u.active', 1)
                ->where('u.employee_type', 'internal')
                ->whereNull('u.deleted_at')
                ->distinct()
                ->count('s.user_id');

            // --- Return dataset ---
            return [
                'internal_users'        => $internal_users,
                'published_courses'     => $published_courses,
                'departments'           => $departments,
                'categories'            => $categories,
                'students_count'        => $students_count,
                'teachers_count'        => $teachers_count,
                'courses_count'         => $courses_count,
                'recent_orders'         => $recent_orders,
                'recent_subscriptions'  => $recent_subscriptions,
                'recent_contacts'       => $recent_contacts,
                'total_assignments'     => $total_assignments,
                'total_certificate_issued' => $total_certificate_issued,
                'assigned_users_count'  => $assigned_users_count,
                'course_completion' => [
                    'completed'          => $completedCount,
                    'not_completed'      => $notCompletedCount,
                    'avg_completion_rate' => $av_completion_rate,
                ],
                'assessment_stats' => [
                    'completed'          => $completed_assessments,
                    'not_completed'      => $not_completed_assessments,
                    'avg_score'          => $av_completed_score,
                ],
            ];
        });

        return $adminStats;
    }


    /**
     * @return \Illuminate\View\View
     */
    public function index()
    {
        //dd("dash");
        $av_completed_score = 0;
        $av_not_completed_score = null;
        $categories = null;
        $departments = null;
        $published_courses = null;
        $internal_users = null;
        $av_rem_completion_rate = null;
        $av_completion_rate = 0;
        $total_certificate_issued = null;
        $total_assignments = null;
        $assigned_users_count = null;
        $purchased_courses = NULL;
        $students_count = NULL;
        $recent_reviews = NULL;
        $threads = NULL;
        $teachers_count = NULL;
        $courses_count = NULL;
        $pending_orders = NULL;
        $recent_orders = NULL;
        $recent_contacts = NULL;
        $purchased_bundles = NULL;
        $subscribe_courses = NULL;
        $recent_subscriptions = NULL;
        $subscribed_bundles = NULL;
        $subscribed_courses = NULL;

        $total_completed = null;
        $total_pending = null;

        $completed_assesment = null;
        $not_completed_assesment = null;

        $cache_duration = 10;


        //dd(CustomHelper::progress(51));
        if (\Auth::check()) {


            $user = auth()->user();
            $userId = $user->id;



            if (auth()->user()->hasRole('teacher')) {
                //IF logged in user is teacher

                $user = auth()->user();
                $cacheKey = "teacher_dashboard_stats_{$user->id}";

                if (Cache::has($cacheKey)) {
                    $adminStats = Cache::get($cacheKey);
                    if ($adminStats) {
                        extract($adminStats);
                    }
                } else {
                    $adminStats = self::buildDashboardCache(10, $user);
                    if ($adminStats) {
                        extract($adminStats);
                    }
                }

                $recent_reviews = [];
                $threads = [];

                $av_completion_rate       = $adminStats['course_completion']['avg_completion_rate'] ?? 0;
                $total_completed          = $adminStats['course_completion']['completed'] ?? 0;
                $total_pending            = $adminStats['course_completion']['not_completed'] ?? 0;

                $av_completed_score        = $adminStats['assessment_stats']['avg_score'] ?? 0;
                $completed_assesment       = $adminStats['assessment_stats']['completed'] ?? 0;
                $not_completed_assesment   = $adminStats['assessment_stats']['not_completed'] ?? 0;

                return view('backend.dashboard', array_merge(
                    compact('adminStats'),
                    compact(
                        'threads',
                        'recent_reviews',
                        'departments',
                        'internal_users',
                        'students_count',
                        'teachers_count',
                        'published_courses',
                        'categories',
                        'courses_count',
                        'recent_orders',
                        'recent_subscriptions',
                        'recent_contacts',
                        'total_assignments',
                        'total_certificate_issued',
                        'assigned_users_count',
                        'av_completion_rate',
                        'total_completed',
                        'total_pending',
                        'av_completed_score',
                        'completed_assesment',
                        'not_completed_assesment'
                    )
                ));

            } elseif (auth()->user()->hasRole('administrator')) {

                //dd("admin");
                $userId = auth()->user()->id;
                $cacheKey = "admin_dashboard_stats_{$userId}";

                if (Cache::has($cacheKey)) {
                    $adminStats = Cache::get($cacheKey);
                    if ($adminStats) {
                        extract($adminStats);
                    }
                } else {
                    $adminStats = self::buildDashboardCache(10, auth()->user());
                    if ($adminStats) {
                        extract($adminStats);
                    }
                }

                //dd($adminStats);

                $av_completion_rate       = $adminStats['course_completion']['avg_completion_rate'] ?? 0;
                $total_completed          = $adminStats['course_completion']['completed'] ?? 0;
                $total_pending            = $adminStats['course_completion']['not_completed'] ?? 0;

                $av_completed_score        = $adminStats['assessment_stats']['avg_score'] ?? 0;
                $completed_assesment       = $adminStats['assessment_stats']['completed'] ?? 0;
                $not_completed_assesment   = $adminStats['assessment_stats']['not_completed'] ?? 0;

                return view('backend.dashboard', array_merge(
                    compact('adminStats'),
                    compact(
                        'departments',
                        'internal_users',
                        'students_count',
                        'teachers_count',
                        'published_courses',
                        'categories',
                        'courses_count',
                        'recent_orders',
                        'recent_subscriptions',
                        'recent_contacts',
                        'total_assignments',
                        'total_certificate_issued',
                        'assigned_users_count',
                        'av_completion_rate',
                        'total_completed',
                        'total_pending',
                        'av_completed_score',
                        'completed_assesment',
                        'not_completed_assesment'
                    )
                ));
            } elseif (auth()->user()->employee_type == 'internal') {


                $learning_pathways = null;

                //dd($this->cacheWritable());

                if ($this->cacheWritable()) {
                    try {
                        $course_completion_data = Cache::remember("course_completion_data_user_{$userId}", now()->addMinutes($cache_duration), function () use ($userId) {
                            return DB::table('subscribe_courses as s')
                                ->whereNull('s.deleted_at')
                                ->where('s.user_id', $userId)
                                ->selectRaw('
                                    COUNT(CASE WHEN s.is_completed = 1 THEN 1 END) as completed_count,
                                    COUNT(CASE WHEN s.is_completed = 0 THEN 1 END) as not_completed_count
                                ')
                                ->first();
                        });
                    } catch (\Throwable $e) {
                        //dd($e->getMessage());
                        $course_completion_data = DB::table('subscribe_courses as s')
                            ->whereNull('s.deleted_at')
                            ->where('s.user_id', $userId)
                            ->selectRaw('
                                COUNT(CASE WHEN s.is_completed = 1 THEN 1 END) as completed_count,
                                COUNT(CASE WHEN s.is_completed = 0 THEN 1 END) as not_completed_count
                            ')
                            ->first();
                    }

                    //dd($course_completion_data);
                } else {
                    $course_completion_data = DB::table('subscribe_courses as s')
                        ->whereNull('s.deleted_at')
                        ->where('s.user_id', $userId)
                        ->selectRaw('
                                COUNT(CASE WHEN s.is_completed = 1 THEN 1 END) as completed_count,
                                COUNT(CASE WHEN s.is_completed = 0 THEN 1 END) as not_completed_count
                            ')
                        ->first();
                }

                $completedCount    = $course_completion_data->completed_count ?? 0;
                $notCompletedCount = $course_completion_data->not_completed_count ?? 0;

                $total_rows = $completedCount + $notCompletedCount;

                // Avoid division by zero
                $av_completion_rate = $total_rows > 0
                    ? round(($completedCount / $total_rows) * 100, 0)
                    : 0;

                $av_rem_completion_rate = 100 - $av_completion_rate;

                $total_completed = $completedCount;
                $total_pending   = $notCompletedCount;

                try {
                    $assessmentStats = Cache::remember(
                        "assessment_stats_{$userId}",
                        now()->addMinutes($cache_duration),
                        function () use ($userId) {

                            $totalScore = 0;
                            $totalAssessmentsTaken = 0;
                            $total_certificate_issued = 0;

                            $subscriptions = SubscribeCourse::where('user_id', $userId)
                                ->whereHas('course.courseAssignments')
                                ->select('assesment_taken', 'assignment_score', 'grant_certificate')
                                ->get();

                            $totalAssessments = $subscriptions->count();

                            foreach ($subscriptions as $subscription) {
                                if ($subscription->assesment_taken) {
                                    $totalAssessmentsTaken++;
                                    $score = (float) rtrim($subscription->assignment_score, '%');
                                    $totalScore += $score;
                                }

                                if ($subscription->grant_certificate) {
                                    $total_certificate_issued++;
                                }
                            }

                            $completedAssessments = $totalAssessmentsTaken;
                            $notCompletedAssessments = $totalAssessments - $totalAssessmentsTaken;

                            $av_completed_score = $totalAssessmentsTaken
                                ? round($totalScore / $totalAssessmentsTaken, 0)
                                : 0;

                            return [
                                'completed' => $completedAssessments,
                                'not_completed' => $notCompletedAssessments,
                                'avg_score' => $av_completed_score,
                                'total' => $totalAssessments,
                                'total_certificate_issued' => $total_certificate_issued,
                            ];
                        }
                    );
                } catch (\Throwable $e) {


                    // Fallback: If cache fails, run the same calculation WITHOUT cache
                    $subscriptions = SubscribeCourse::where('user_id', $userId)
                        ->whereHas('course.courseAssignments')
                        ->select('assesment_taken', 'assignment_score', 'grant_certificate')
                        ->get();

                    $totalScore = 0;
                    $totalAssessmentsTaken = 0;
                    $total_certificate_issued = 0;

                    $totalAssessments = $subscriptions->count();

                    foreach ($subscriptions as $subscription) {
                        if ($subscription->assesment_taken) {
                            $totalAssessmentsTaken++;
                            $score = (float) rtrim($subscription->assignment_score, '%');
                            $totalScore += $score;
                        }

                        if ($subscription->grant_certificate) {
                            $total_certificate_issued++;
                        }
                    }

                    $completedAssessments = $totalAssessmentsTaken;
                    $notCompletedAssessments = $totalAssessments - $totalAssessmentsTaken;

                    $av_completed_score = $totalAssessmentsTaken
                        ? round($totalScore / $totalAssessmentsTaken, 0)
                        : 0;

                    $assessmentStats = [
                        'completed' => $completedAssessments,
                        'not_completed' => $notCompletedAssessments,
                        'avg_score' => $av_completed_score,
                        'total' => $totalAssessments,
                        'total_certificate_issued' => $total_certificate_issued,
                    ];
                }


                $completed_assesment     = $assessmentStats['completed'];
                $not_completed_assesment  = $assessmentStats['not_completed'];
                $av_completed_score       = $assessmentStats['avg_score'];
                $totalAssessments         = $assessmentStats['total'];
                $total_certificate_issued = $assessmentStats['total_certificate_issued'];
            }else {
                //dd("admin");
                $userId = auth()->user()->id;
                $cacheKey = "user_dashboard_stats_{$userId}";

                if (Cache::has($cacheKey)) {
                    $adminStats = Cache::get($cacheKey);
                    if ($adminStats) {
                        extract($adminStats);
                    }
                } else {
                    $adminStats = self::buildDashboardCache(10, auth()->user());
                    if ($adminStats) {
                        extract($adminStats);
                    }
                }

                //dd($adminStats);

                $av_completion_rate       = $adminStats['course_completion']['avg_completion_rate'] ?? 0;
                $total_completed          = $adminStats['course_completion']['completed'] ?? 0;
                $total_pending            = $adminStats['course_completion']['not_completed'] ?? 0;

                $av_completed_score        = $adminStats['assessment_stats']['avg_score'] ?? 0;
                $completed_assesment       = $adminStats['assessment_stats']['completed'] ?? 0;
                $not_completed_assesment   = $adminStats['assessment_stats']['not_completed'] ?? 0;

                return view('backend.dashboard', array_merge(
                    compact('adminStats'),
                    compact(
                        'departments',
                        'internal_users',
                        'students_count',
                        'teachers_count',
                        'published_courses',
                        'categories',
                        'courses_count',
                        'recent_orders',
                        'recent_subscriptions',
                        'recent_contacts',
                        'total_assignments',
                        'total_certificate_issued',
                        'assigned_users_count',
                        'av_completion_rate',
                        'total_completed',
                        'total_pending',
                        'av_completed_score',
                        'completed_assesment',
                        'not_completed_assesment'
                    )
                ));
            }
        }


        return view('backend.dashboard', compact('not_completed_assesment', 'completed_assesment', 'total_completed', 'total_pending', 'av_completed_score', 'av_not_completed_score', 'categories', 'departments', 'published_courses', 'internal_users', 'av_rem_completion_rate', 'av_completion_rate', 'total_certificate_issued', 'total_assignments', 'assigned_users_count', 'subscribe_courses', 'purchased_courses', 'students_count', 'recent_reviews', 'threads', 'purchased_bundles', 'teachers_count', 'courses_count', 'recent_orders', 'recent_contacts', 'pending_orders', 'subscribed_courses', 'subscribed_bundles', 'recent_subscriptions', 'learning_pathways'));
    }


    private function cacheWritable()
    {
        $main = storage_path('framework/cache/data');

        if (!is_dir($main)) {
            @mkdir($main, 0777, true);
        }

        // check after creating
        return is_dir($main) && is_writable($main);
    }


    public function getDashboardStats(Request $request)
    {

        $course_ids = [];
        $user_ids = [];

        if ($request->course_id) {
            $course_ids[] = $request->course_id;
        }

        if ($request->user_id) {
            $user_ids[] = $request->user_id;
        }

        $department_id = $request->department_id ?? null;

        $category_id = $request->category_id ?? null;

        if ($department_id) {
            $user_ids = EmployeeProfile::where('department', $department_id)->pluck('user_id')->toArray();
        }

        //dd($user_ids);

        if ($category_id) {
            $course_ids = Course::where('category_id', $category_id)->pluck('id')->toArray();
        }

        //dd($course_ids, $user_ids);

        $course_completion_data = DB::table('subscribe_courses as s')
            ->whereNull('s.deleted_at')
            ->when(count($course_ids), function ($q) use ($course_ids) {
                $q->whereIn('s.course_id', $course_ids);
            })
            ->when(count($user_ids), function ($q) use ($user_ids) {
                $q->whereIn('s.user_id', $user_ids);
            })

            //->whereIn('s.user_id' , $user_ids)

            ->selectRaw('
                                                COUNT(CASE WHEN s.is_completed = 1 THEN 1 END) as completed_count,
                                                COUNT(CASE WHEN s.is_completed = 0 THEN 1 END) as not_completed_count
                                            ')
            ->first();

        //dd($course_completion_data);

            $course_completion_data->completed_count ?? 0;
            $course_completion_data->not_completed_count ?? 0;

        $total_rows = $course_completion_data->completed_count + $course_completion_data->not_completed_count;



        $av_completion_rate = $total_rows > 0 ? round(($course_completion_data->completed_count / $total_rows * 100), 0) : 0;


        $total_completed_data = DB::table('subscribe_courses as s')
            ->whereNull('s.deleted_at')
            ->where('s.is_completed', 1)
            ->when(count($course_ids), function ($q) use ($course_ids) {
                $q->whereIn('s.course_id', $course_ids);
            })
            ->when(count($user_ids), function ($q) use ($user_ids) {
                $q->whereIn('s.user_id', $user_ids);
            })
            ->selectRaw('
                                            SUM(CAST(REPLACE(s.assignment_score, "%", "") AS UNSIGNED)) as total_score,
                                            COUNT(CASE WHEN s.is_completed = 1 AND CAST(REPLACE(s.assignment_score, "%", "") AS UNSIGNED) > 0 THEN 1 END) as completed_count
                                        ')
            ->first();



        $av_completed_score = $total_completed_data->completed_count > 0 ? round(($total_completed_data->total_score / ($total_completed_data->completed_count * 100)) * 100, 0) : 0;


        $total_completed = $course_completion_data->completed_count ?? 0;
        $total_pending = $course_completion_data->not_completed_count;


        $totalScoreData = SubscribeCourse::whereIn('user_id', $user_ids)
            ->whereHas('course.courseAssignments')
            ->get();

        $totalScore = 0;
        $total_assesment = 0;
        $total_assesment_taken = 0;
        $completed_assesment = 0;

        foreach ($totalScoreData as $subscription) {
            if ($subscription->assesment_taken) {
                $total_assesment_taken++;
            }
            if ($subscription->assignment_score && $subscription->assesment_taken) {
                // Remove % and convert to integer
                $score = (int) str_replace('%', '', $subscription->assignment_score);
                $totalScore += $score;
            }
            $total_assesment++;
        }

        $completed_assesment = $total_assesment_taken;
        $not_completed_assesment = $total_assesment - $total_assesment_taken;
        if ($completed_assesment) {
            $av_completed_score = $total_assesment_taken > 0 ? round($totalScore / $total_assesment_taken, 0) : 0;
        } else {
            $av_completed_score = 0;
        }



        return response()->json([
            'total_completed' => $total_completed,
            'total_pending' => $total_pending,
            'av_completion_rate' => $av_completion_rate,
            //'av_rem_completion_rate' => $av_rem_completion_rate,

            'av_completed_score' => $av_completed_score,
            'completed_assesment' => $completed_assesment,
            'not_completed_assesment' => $not_completed_assesment,
        ]);
    }




    public function setvaluesession($type)
    {
        Session::put('setvaluesession', $type);
        //print_r(Session::get('setvaluesession'));die;
        return back();
    }
}