<?php

namespace App\Http\Controllers;

use App\Models\Blog;
use App\Models\Bundle;
use App\Models\Category;
use App\Models\Course;
use App\Models\Events;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Stripe\Stripe;
use Stripe\Charge;
use Stripe\Customer;
use Cart;
use App\Models\stripe\Subscription;
use Auth;
use Carbon\Carbon;
use DB;

class CalenderController extends Controller
{

    private $path;

    public function __construct()
    {
        $path = 'frontend';
        if (session()->has('display_type')) {
            if (session('display_type') == 'rtl') {
                $path = 'frontend-rtl';
            } else {
                $path = 'frontend';
            }
        } else if (config('app.display_type') == 'rtl') {
            $path = 'frontend-rtl';
        }
        $this->path = $path;
    }

    public function show_list()
    {
        $logged_in_user = Auth::user();
        $employee_id = $logged_in_user->id;
        $isAdmin = $logged_in_user->isAdmin();
        $isTeacher = $logged_in_user->hasRole('teacher');

        // ─── 1. Regular Lessons (existing) ───
        $lesson_data = [];
        if ($isAdmin) {
            $lessons_data = DB::table('lessons')
                ->select('courses.title as course_name', 'courses.slug as course_slug', 'lessons.title', 'lessons.lesson_start_date')
                ->leftJoin('courses', 'courses.id', 'lessons.course_id')
                ->whereNull('courses.deleted_at')
                ->get();
        } elseif ($isTeacher) {
            $lessons_data = DB::table('lessons')
                ->select('courses.title as course_name', 'courses.slug as course_slug', 'lessons.title', 'lessons.lesson_start_date')
                ->leftJoin('courses', 'courses.id', 'lessons.course_id')
                ->join('course_user', 'course_user.course_id', '=', 'courses.id')
                ->where('course_user.user_id', $employee_id)
                ->whereNull('courses.deleted_at')
                ->get();
        } else {
            $lessons_data = DB::table('subscribe_courses')
                ->select('courses.title as course_name', 'courses.slug as course_slug', 'lessons.title', 'lessons.lesson_start_date')
                ->leftJoin('courses', 'courses.id', 'subscribe_courses.course_id')
                ->leftJoin('lessons', 'lessons.course_id', 'courses.id')
                ->where('subscribe_courses.user_id', $employee_id)
                ->whereNull('courses.deleted_at')
                ->get();
        }

        if ($lessons_data) {
            foreach ($lessons_data as $key => $data) {
                if (!$data->lesson_start_date) continue;
                $lesson_data[] = [
                    'title'       => $data->title,
                    'url'         => route('courses.show', $data->course_slug),
                    'start'       => date('Y-m-d', strtotime($data->lesson_start_date)),
                    'description' => $data->course_name,
                ];
            }
        }

        // ─── 2. Course-Level Live Sessions (Zoom/Teams/Meet) ───
        $live_session_data = [];
        $meetingQuery = DB::table('courses')
            ->select(
                'courses.title', 'courses.slug', 'courses.meeting_provider',
                'courses.meeting_start_at', 'courses.meeting_duration',
                'courses.meeting_join_url', 'courses.meeting_host_url'
            )
            ->whereNotNull('courses.meeting_start_at')
            ->whereNotNull('courses.meeting_provider')
            ->whereNull('courses.deleted_at');

        if ($isAdmin) {
            // no additional filter
        } elseif ($isTeacher) {
            $meetingQuery->join('course_user', 'course_user.course_id', '=', 'courses.id')
                ->where('course_user.user_id', $employee_id);
        } else {
            $meetingQuery->join('subscribe_courses', 'subscribe_courses.course_id', '=', 'courses.id')
                ->where('subscribe_courses.user_id', $employee_id);
        }

        $meetings_data = $meetingQuery->get();

        foreach ($meetings_data as $data) {
            $providerLabel = ucfirst($data->meeting_provider);
            $start = Carbon::parse($data->meeting_start_at);
            // Teachers/Admins get host link, students get join link
            $meetingUrl = ($isAdmin || $isTeacher)
                ? ($data->meeting_host_url ?: $data->meeting_join_url)
                : $data->meeting_join_url;
            $event = [
                'title' => "[$providerLabel] " . $data->title,
                'start' => $start->toIso8601String(),
                'url'   => $meetingUrl ?: route('courses.show', $data->slug),
            ];
            if ($data->meeting_duration) {
                $event['end'] = $start->copy()->addMinutes((int)$data->meeting_duration)->toIso8601String();
            }
            $live_session_data[] = $event;
        }

        // ─── 3. Live Lesson Slots ───
        $live_slot_data = [];
        $slotQuery = DB::table('live_lesson_slots')
            ->select(
                'live_lesson_slots.topic', 'live_lesson_slots.start_at',
                'live_lesson_slots.duration', 'live_lesson_slots.join_url',
                'live_lesson_slots.start_url',
                'courses.title as course_name', 'courses.slug as course_slug',
                'lessons.title as lesson_title'
            )
            ->join('lessons', 'lessons.id', '=', 'live_lesson_slots.lesson_id')
            ->join('courses', 'courses.id', '=', 'lessons.course_id')
            ->whereNull('live_lesson_slots.deleted_at')
            ->whereNull('courses.deleted_at');

        if ($isAdmin) {
            // no additional filter
        } elseif ($isTeacher) {
            $slotQuery->join('course_user', 'course_user.course_id', '=', 'courses.id')
                ->where('course_user.user_id', $employee_id);
        } else {
            $slotQuery->join('subscribe_courses', 'subscribe_courses.course_id', '=', 'courses.id')
                ->where('subscribe_courses.user_id', $employee_id);
        }

        $slots_data = $slotQuery->get();

        foreach ($slots_data as $data) {
            $start = Carbon::parse($data->start_at);
            // Teachers/Admins get host (start) link, students get join link
            $slotUrl = ($isAdmin || $isTeacher)
                ? ($data->start_url ?: $data->join_url)
                : $data->join_url;
            $event = [
                'title' => '[Live] ' . ($data->topic ?: $data->lesson_title),
                'start' => $start->toIso8601String(),
                'url'   => $slotUrl ?: route('courses.show', $data->course_slug),
            ];
            if ($data->duration) {
                $event['end'] = $start->copy()->addMinutes((int)$data->duration)->toIso8601String();
            }
            $live_slot_data[] = $event;
        }

        return view($this->path . '.calender.index', [
            'lessons'          => json_encode($lesson_data),
            'liveSessions'     => json_encode($live_session_data),
            'liveLessonSlots'  => json_encode($live_slot_data),
        ]);
    }

    public function all()
    {
        if (request('type') == 'popular') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->where('popular', '=', 1)->orderBy('id', 'desc')->paginate(9);

        } else if (request('type') == 'trending') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->where('trending', '=', 1)->orderBy('id', 'desc')->paginate(9);

        } else if (request('type') == 'featured') {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->where('featured', '=', 1)->orderBy('id', 'desc')->paginate(9);

        } else {
            $courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', 1)->orderBy('id', 'desc')->paginate(9);
        }
        $purchased_courses = NULL;
        $purchased_bundles = NULL;
        $categories = Category::where('status', '=', 1)->get();

        if (\Auth::check()) {
            $purchased_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->whereHas('students', function ($query) {
                $query->where('id', \Auth::id());
            })
                ->with('lessons')
                ->orderBy('id', 'desc')
                ->get();
        }
        $featured_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', '=', 1)
            ->where('featured', '=', 1)->take(8)->get();

        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        return view($this->path . '.courses.index', compact('courses', 'purchased_courses', 'recent_news', 'featured_courses', 'categories'));
    }

    public function show($course_slug)
    {
        //dd('gg');
        $continue_course = NULL;
        $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
        $course = Course::withoutGlobalScope('filter')->where('slug', $course_slug)->with('publishedLessons')->firstOrFail();
        $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
        if (($course->published == 0) && ($purchased_course == false)) {
            abort(404);
        }
        $course_rating = 0;
        $total_ratings = 0;
        $completed_lessons = "";
        $is_reviewed = false;
        if (auth()->check() && $course->reviews()->where('user_id', '=', auth()->user()->id)->first()) {
            $is_reviewed = true;
        }
        if ($course->reviews->count() > 0) {
            $course_rating = $course->reviews->avg('rating');
            $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
        }
        $lessons = $course->courseTimeline()->orderby('id', 'asc')->get();
        $checkSubcribePlan=[];
        if (\Auth::check()) {

            $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
            $course_lessons = $course->lessons->pluck('id')->toArray();
            $continue_course = $course->courseTimeline()
                ->whereIn('model_id', $course_lessons)
                ->orderby('sequence', 'asc')
                ->whereNotIn('model_id', $completed_lessons)
                ->first();
            if ($continue_course == null) {
                $continue_course = $course->courseTimeline()
                    ->whereIn('model_id', $course_lessons)
                    ->orderby('sequence', 'asc')->first();
            }
           //$checkSubcribePlan = auth()->user()->checkPlanSubcribeUser();
           $checkSubcribePlan = '';
        }
        $courseInPlan = courseOrBundlePlanExits($course->id,'');
        return view($this->path . '.courses.course', compact('course', 'purchased_course', 'recent_news', 'course_rating', 'completed_lessons', 'total_ratings', 'is_reviewed', 'lessons', 'continue_course', 'checkSubcribePlan','courseInPlan'));
    }


    public function rating($course_id, Request $request)
    {
        $course = Course::findOrFail($course_id);
        $course->students()->updateExistingPivot(\Auth::id(), ['rating' => $request->get('rating')]);

        return redirect()->back()->with('success', 'Thank you for rating.');
    }

    public function getByCategory(Request $request)
    {
        $category = Category::where('slug', '=', $request->category)
            ->where('status', '=', 1)
            ->first();
        $categories = Category::where('status', '=', 1)->get();

        if ($category != "") {
            $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
            $featured_courses = Course::where('published', '=', 1)
                ->where('featured', '=', 1)->take(8)->get();

            if (request('type') == 'popular') {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->where('popular', '=', 1)->orderBy('id', 'desc')->paginate(9);

            } else if (request('type') == 'trending') {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->where('trending', '=', 1)->orderBy('id', 'desc')->paginate(9);

            } else if (request('type') == 'featured') {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->where('featured', '=', 1)->orderBy('id', 'desc')->paginate(9);

            } else {
                $courses = $category->courses()->withoutGlobalScope('filter')->where('published', 1)->orderBy('id', 'desc')->paginate(9);
            }


            return view($this->path . '.courses.index', compact('courses', 'category', 'recent_news', 'featured_courses', 'categories'));
        }
        return abort(404);
    }

    public function addReview(Request $request)
    {
        $this->validate($request, [
            'review' => 'required'
        ]);
        $course = Course::findORFail($request->id);
        $review = new Review();
        $review->user_id = auth()->user()->id;
        $review->reviewable_id = $course->id;
        $review->reviewable_type = Course::class;
        $review->rating = $request->rating;
        $review->content = $request->review;
        $review->save();

        return back();
    }

    public function editReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $course = $review->reviewable;
            $recent_news = Blog::orderBy('created_at', 'desc')->take(2)->get();
            $purchased_course = \Auth::check() && $course->students()->where('user_id', \Auth::id())->count() > 0;
            $course_rating = 0;
            $total_ratings = 0;
            $lessons = $course->courseTimeline()->orderby('sequence', 'asc')->get();

            if ($course->reviews->count() > 0) {
                $course_rating = $course->reviews->avg('rating');
                $total_ratings = $course->reviews()->where('rating', '!=', "")->get()->count();
            }
            if (\Auth::check()) {

                $completed_lessons = \Auth::user()->chapters()->where('course_id', $course->id)->get()->pluck('model_id')->toArray();
                $continue_course = $course->courseTimeline()->orderby('sequence', 'asc')->whereNotIn('model_id', $completed_lessons)->first();
                if ($continue_course == "") {
                    $continue_course = $course->courseTimeline()->orderby('sequence', 'asc')->first();
                }

            }
            return view($this->path . '.courses.course', compact('course', 'purchased_course', 'recent_news', 'completed_lessons', 'continue_course', 'course_rating', 'total_ratings', 'lessons', 'review'));
        }
        return abort(404);

    }


    public function updateReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $review->rating = $request->rating;
            $review->content = $request->review;
            $review->save();

            return redirect()->route('courses.show', ['slug' => $review->reviewable->slug]);
        }
        return abort(404);

    }

    public function deleteReview(Request $request)
    {
        $review = Review::where('id', '=', $request->id)->where('user_id', '=', auth()->user()->id)->first();
        if ($review) {
            $slug = $review->reviewable->slug;
            $review->delete();
            return redirect()->route('courses.show', ['slug' => $slug]);
        }
        return abort(404);
    }

    public function add_event(Request $request)
    {
        $request->validate([
        'title' => 'required|string|max:255',
        'content' => 'required|string',
        'event_date' => 'required|date|after_or_equal:today',
    ]);
        // dd($request->all());
        $event = new Events;
        $event->title = $request->title;
        $event->content = $request->content;
        $event->event_date = $request->event_date;
        //
        $event->save();
        // dd($event->save());
        return redirect()->route('user.calender');
    }
}
