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
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Str;

class CourseFeebackController extends Controller
{

    public function index(Request $request)
    {

        // $courses = Course::get();
        $courses = $courses = Course::has('category')->whereHas('courseFeedback')->pluck('title', 'id')->prepend('All', '');


        if ($request->ajax()) {
            $course_id = $request->course_id;

            $query = CourseFeedback::query()->with('course','feedback')
                                ->whereHas('course')
                                ->whereHas('feedback');

                    if ($course_id) {
                        $query->where('course_id', $course_id);
                    }

            return DataTables::of($query)
                    ->addIndexColumn()
                    ->addColumn('course_name', function ($single) {
                        return $single->course->title ?? 'N/A';
                    })
                    ->addColumn('question', function ($single) {
                        return $single->feedback->question ?? 'N/A';
                    })
                    ->filter(function ($instance) use ($request) {
                        if (!empty($request->get('search')['value'])) {
                            $search = Str::lower($request->get('search')['value']);
                            $instance->collection = $instance->collection->filter(function ($row) use ($search) {
                                return Str::contains(Str::lower($row['course_name']), $search)
                                    || Str::contains(Str::lower($row['question']), $search)
                                    || Str::contains(Str::lower($row['answer'] ?? ''), $search);
                            });
                        }
                    })
                    ->addColumn('actions', function ($single) {

                        $edit_route = route('admin.course.coursefeedbackquestion.edit',[$single->id]);

                        $actions = '<div class="action-pill">';
                        // $actions .= '<a title="Edit" href="'.$edit_route.'">
                        //                 <i class="fa fa-edit"></i>
                        //             </a>';
                        $actions .= '<a title="Delete" href="#" 
                                        data-name="course feedback question" 
                                        data-type="delete" 
                                        data-url="/user/course-feedback-questions/delete/' . $single->id . '">
                                        <i class="fa fa-trash"></i>
                                    </a>';
                        $actions .= '</div>';
                        return $actions;
                    })
                    ->rawColumns(['actions', 'question'])
                    ->make(true);
        }


        return view('backend.course_feedback_question.index', compact('courses'));
    }

    public function destroy(Request $request)
    {
        CourseFeedback::where('course_id', $request->id)->delete();
    }

    public function edit(Request $request)
    {
        $cf = CourseFeedback::where('course_id', $request->id)->first();
        
        if(isset($cf)) {
            $cf->feedback_question_id = CourseFeedback::where('course_id', $request->id)->pluck('feedback_question_id')->toArray();
        }
        
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
