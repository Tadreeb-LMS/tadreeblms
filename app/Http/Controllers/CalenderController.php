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
        /*
        $data = array(

            array(
               'id' => 1,
               'title' => "Event1",
               'start' => "2022-05-05",
               'url' => "http://yahoo.com/"
            ),

            array(
               'id' => 2,
               'title' => "Event2",
               'start' => "2022-05-05",
               'end' => "2022-05-05",
               'url' => "http://yahoo.com/"
            )

        );
        */
        $logged_in_user = Auth::user();
        $employee_id = $logged_in_user->id;
$lesson_data=[];
        if($logged_in_user->isAdmin()) {
            $lessons_data = DB::table('lessons')->select('courses.title as course_name','courses.slug as course_slug','lessons.title','lessons.lesson_start_date')
                                ->leftJoin('courses','courses.id','lessons.course_id');
            $lessons_data = $lessons_data->get();
        } else {
            $lessons_data = DB::table('course_student')->select('course_student.course_id','courses.title as course_name','courses.slug as course_slug','lessons.title','lessons.lesson_start_date')
            ->leftJoin('courses','courses.id','course_student.course_id')
            ->leftJoin('lessons','lessons.course_id','courses.id');
            $lessons_data = $lessons_data->where('user_id',$employee_id);
            $lessons_data = $lessons_data->get();
        }



        //dd($lessons_data);
        if( $lessons_data ) {
            foreach($lessons_data as $key=>$data) {
                $lesson_data[$key]['title'] = $data->title;
                $lesson_data[$key]['url'] = route('courses.show',$data->course_slug);
                $lesson_data[$key]['start'] = date('Y-m-d',strtotime($data->lesson_start_date));
                $lesson_data[$key]['description'] = $data->course_name;

            }
        }

        $lessons = json_encode($lesson_data);
        return view($this->path . '.calender.index', ['lessons'=>$lessons]);

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
