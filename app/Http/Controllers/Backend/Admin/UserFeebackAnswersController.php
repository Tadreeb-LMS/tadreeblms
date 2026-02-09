<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Models\Course;
use App\Models\CourseTimeline;
use App\Models\Lesson;
use App\Models\Media;
use App\Models\Test;
use App\Helpers\CustomHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Gate;
use App\Http\Controllers\Controller;
use App\Models\CourseFeedback;
use App\Models\FeedbackQuestion;
use App\Models\UserFeedback;
use DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class UserFeebackAnswersController extends Controller
{

    public function index_(Request $request)
    {


        if ($request->ajax()) {
            $userFeedbackAnswers = UserFeedback::whereHas('courseFeedback')->latest();

            return DataTables::of($userFeedbackAnswers)
                ->addIndexColumn()
                ->addColumn('user_name', function ($single) {
                    return @$single->user->full_name;
                })
                ->addColumn('course_name', function ($single) {
                    return @$single->course->title;
                })
                ->addColumn('question_answers', function ($single) {
                    return $single->question_answers;
                })
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if (!empty($search)) {
                        $query->whereHas('user', function ($query) use ($search) {
                            $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                        })
                            ->orWhereHas('course', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                            });
                    }
                })
                ->rawColumns(['question_answers'])
                ->make(true);
        }

        return view('backend.course_feedback_answer.index');
    }

    public function index(Request $request)
    {


        if ($request->ajax()) {
            $userFeedbackAnswers = UserFeedback::select('user_feedback.*')
                                    ->whereHas('courseFeedback')
                                    ->whereIn('id', function($q) {
                                        $q->selectRaw('MAX(id)')
                                        ->from('user_feedback')
                                        ->groupBy('user_id', 'course_id');
                                    })
                                    ->latest();
                                    

            //dd($userFeedbackAnswers);

            return DataTables::of($userFeedbackAnswers)
                ->addIndexColumn()
                ->addColumn('user_name', function ($single) {
                    return @$single->user->full_name;
                })
                ->addColumn('course_name', function ($single) {
                    return @$single->course->title;
                })
                ->addColumn('submitted_on', function ($single) {
                    return $single->created_at ? $single->created_at->format('d M Y h:i A') : '-';
                })
                ->addColumn('question_answers', function ($single) {
                    return '<a class="badge badge-info feedback-detail" data-id="' . $single->id . '" href="#"> Detail </a>'; //$single->question_answers;
                })
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search.value');

                    if (!empty($search)) {
                            $query->where(function ($q) use ($search) {
                                $q->whereHas('user', function ($q2) use ($search) {
                                    $q2->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                                })
                                ->orWhereHas('course', function ($q2) use ($search) {
                                    $q2->where('title', 'like', "%{$search}%");
                                });
                            })->distinct();
                    }
                })
                ->rawColumns(['question_answers'])
                ->make(true);
        }

        return view('backend.course_feedback_answer.index');
    }

    public function feedback_detail(Request $request, $id)
    {
        // Example: fetch feedback from DB
        
        $feedback = UserFeedback::find($id);

        if (!$feedback) {
            return response()->json(['html' => '<p>No feedback found.</p>']);
        }

        //dd($feedback->question_answers);

        return response()->json(['html' => $feedback->question_answers]);
    }

    public function destroy(Request $request)
    {
        CourseFeedback::where('course_id', $request->id)->delete();
    }

    public function edit(Request $request)
    {
        $cf = CourseFeedback::where('course_id', $request->id)->first();
        @$cf->feedback_question_id = CourseFeedback::where('course_id', $request->id)->pluck('feedback_question_id')->toArray();
        $courses = Course::all();
        $questions = FeedbackQuestion::get()->pluck('question', 'id');

        return view('backend.course_feedback_question.edit', compact('cf', 'courses', 'questions'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'course_id' => 'required|numeric|min:1|exists:courses,id',
            'feedback_question_ids' => 'array',
            'feedback_question_ids.*.' => 'numeric|min:1|exists:feedback_questions,id',
        ]);

        CourseFeedback::where('course_id', $request->course_id)->delete();

        $courseFeedback = [];
        foreach ($request->feedback_question_ids as $feedbackQuestion) {
            $courseFeedback[] = [
                'feedback_question_id' => $feedbackQuestion,
                'course_id' => $request->course_id,
                'created_by' => auth()->user()->id,
            ];
        }

        CourseFeedback::insert($courseFeedback);

        return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully']);
        // return redirect()->route('admin.feedback.create_course_feedback')->withFlashSuccess(trans('alerts.backend.general.created'));
    }
}
