<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Exceptions\GeneralException;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Http\Requests\Admin\StoreEmployeeRequest;
use App\Http\Requests\Admin\UpdateEmployeeRequest;
use App\Models\Auth\User;
use App\Models\EmployeeProfile;
use App\Models\Department;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\CourseFeedback;
use App\Models\{FeedbackOption, FeedbackQuestion, UserFeedback};
use App\Models\Stripe\SubscribeCourse;
use App\Models\UserComment;
use App\Models\UserResponse;
use Illuminate\Support\Facades\Gate;
use Yajra\DataTables\DataTables;
use DB;

class FeedbackController extends Controller
{
    use FileUploadTrait;

    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /**course id**/
        $feedbackQuestions = FeedbackQuestion::all();
        $test_questions = DB::table('feedback_questions')->where('feedback_questions.deleted_at', '=', null)->get();

        // dd($feedbackQuestions[0]->id);
        // return view('backend.feedback.index', ['feedback_question_list' => $feedbackQuestions]);
        return view('backend.feedback.index', compact('test_questions'));
    }

    public function edit(Request $request)
    {
        /**course id**/
        $feedbackQuestion = FeedbackQuestion::find($request->id);
        // dd($feedbackQuestion);

        return view('backend.feedback.feedback-question-edit', compact('feedbackQuestion'));
    }

    /**
     * Display a listing of Courses via ajax DataTable.
     *
     * @return \Illuminate\Http\Response
     */
    public function getData(Request $request)
    {
        $has_view = false;
        $has_delete = false;
        $has_edit = false;
        $feedback_questions = "";

        $feedback_questions = FeedbackQuestion::orderBy('created_at', 'desc');

        if (auth()->user()->isAdmin()) {
            $has_view = true;
            $has_edit = true;
            $has_delete = true;
        }
        //     $query = str_replace(array('?'), array('\'%s\''), $teachers->toSql());
        //    $query = vsprintf($query, $teachers->getBindings());
        //    dump($query);
        //    die;
        //$teachers = $teachers->get();
        //dd($teachers);

        return DataTables::of($feedback_questions)
            ->addIndexColumn()
            ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete, $request) {
                $view = "";
                $edit = "";
                $delete = "";
                if ($request->show_deleted == 1) {
                    return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.feedback_question', 'label' => 'id', 'value' => $q->id]);
                }

                // if ($has_view) {
                //     $view = view('backend.datatable.action-view')
                //         ->with(['route' => route('admin.employee.show', ['id' => $q->id])])->render();

                // }

                // if ($has_edit) {

                //     $edit = view('backend.datatable.action-edit')
                //         ->with(['route' => route('admin.employee.edit', ['id' => $q->id])])
                //         ->render();
                //     $view .= $edit;

                // }

                if ($has_delete) {

                    $delete = view('backend.datatable.action-delete')
                        ->with(['route' => route('admin.feedback.destroy', ['id' => $q->id])])
                        ->render();
                    $view .= $delete;
                }

                //$view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

                return $view;
            })
            ->rawColumns(['actions', 'department', 'position', 'image', 'status'])
            ->make();
    }

    /**
     * Show the form for creating new Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function createQuestion()
    {
        return view('backend.feedback.create');
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreTeachersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:255',
        ]);

        $formatted_text = str_replace(['<p>', '</p>'], '', $request->question);

        $feedback = [
            "question" => $formatted_text,
            "created_by" => auth()->user()->id,
        ];

        FeedbackQuestion::create($feedback);

        // return redirect()->route('admin.feedback_question.index')->withFlashSuccess(trans('alerts.backend.general.created'));
        return redirect()->route('admin.employee.index')->withFlashSuccess(trans('Assign trainee for a course'));
    }


    public function createCourseFeedback()
    {
        $courses = Course::all();
        $questions = FeedbackQuestion::get()->pluck('question', 'id');


        return view('backend.feedback.course_feedback', compact('courses', 'questions'));
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreTeachersRequest $request
     * @return \Illuminate\Http\Response
     */
    public function storeCourseFeedback(Request $request)
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

        //update all users of this course whos progress is not 100
        SubscribeCourse::query()
            ->where('course_id', $request->course_id)
            ->where('grant_certificate', 0)
            ->where('has_feedback', 0)
            ->update(['has_feedback' => 1]);

        return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully']);
        // return redirect()->route('admin.feedback.create_course_feedback')->withFlashSuccess(trans('alerts.backend.general.created'));
    }

    public function createFeedbackForm($id, $user_id)
    {
        //dd($id);
        $course = Course::find($id);
        $user = User::find($user_id);

        $questionIds = CourseFeedback::where('course_id', $course->id)->pluck('feedback_question_id')->all();

        $feedbackQuestions = FeedbackQuestion::whereIn('id', $questionIds)->get();
        //dd($feedbackQuestions);
        // show the edit form and pass the shark
        return view('backend.course_feedback.submit_feedback', ['feedback_questions' => $feedbackQuestions, 'course_list' => $course, 'user' => $user]);
    }

    public function feedbackSubmit(Request $request)
    {
        // dd($request->all());
        if ($request->feedback_ids) {
            foreach ($request->feedback_ids as $key => $feedback) {
                UserFeedback::create(
                    [
                        'user_id' => $request->user_id,
                        'course_id' => $request->course_id,
                        'feedback_id' => $feedback,
                        'feedback' => $request->feedback_rates[$key],
                    ]
                );
            }
        }
        return redirect()->route('admin.dashboard');
    }

    // feedback with multiple questions

    public function feedback_questions(Request $request)
    {

        $course = (object)[];
        if (!empty($request->id)) {
            $course = Course::find($request->id);
        }
        //dd($course->id);
        return view('backend.feedback.feedback-question-create', ['course' => $course]);
    }

    public function feedback_questions_store(Request $request)
    {

        if ($request->options) {
            $options = json_decode($request->options);
        }

        $question_id = DB::table('feedback_questions')->insertGetId([
            // 'test_id' => $request->test_id,
            'question_type' => $request->question_type,
            'question' => $request->question,
            'solution' => $request->solution,
            'course_id' => (!empty($request->course_id) ? $request->course_id : 0),
            // 'marks' => $request->marks,
            // 'comment' => $request->comment,
            'option_json' => $request->question_type != 3 ? $request->options : NULL
        ]);

        foreach ($options as $key => $value) {
            DB::table('feedback_option')->insert([
                'question_id' => $question_id,
                'option_text' => $value[0],
                'is_right' => $value[1],
            ]);
        }

        return json_encode(array(
            'code' => 200,
            'course_id' => (!empty($request->course_id) ? $request->course_id : 0),
            'message' => 'Question Inserted'
        ));

        return view('backend.feedback.feedback-question-create');
    }

    public function feedbackQuestionUpdate(Request $request)
    {
        $id = $request->id;
        FeedbackQuestion::where('id', $id)->update([
            'question_type' => $request->question_type,
            'question' => $request->question,
            'solution' => $request->solution,
            'option_json' => $request->question_type != 3 ? $request->options : NULL
        ]);



        if ($request->question_type != 3) {
            $options = json_decode($request->options);
            foreach ($options as $value) {
                DB::table('feedback_option')->insert([
                    'question_id' => $id,
                    'option_text' => $value[0],
                    'is_right' => $value[1],
                ]);
                FeedbackOption::updateOrCreate(['question_id' => $id, 'option_text' => $value[0]], [
                    'is_right' => $value[1],
                ]);
            }
        } else {
            FeedbackOption::where('question_id', $id)->delete();
        }
    }

    public function feedback_questions_delete(Request $request)
    {
        // dd('ji');
        $teacher = FeedbackQuestion::findOrFail($request->id);

        if ($teacher->count() > 0) {
            $teacher->delete();
            return redirect()->route('admin.feedback_question.index')->withFlashDanger(trans('alerts.backend.general.deleted'));
        } else {
            $teacher->delete();
        }

        return json_encode(array(
            'code' => 200,
            'message' => 'Question Deleted '
        ));

        return redirect()->route('admin.feedback_question.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
        return view('backend.feedback.feedback-question-create');
    }

    // public function storeUserResponses(Request $request)
    // {
    //     $request->validate([
    //         'course_id' => 'required|numeric|min:1|exists:courses,id',
    //         'comment' => 'string|max:255',
    //         'feedback_responses' => 'array',
    //         'feedback_responses.*.question_id' =>'numeric|min:1|exists:feedback_questions,id',
    //         'feedback_responses.*.response' =>'numeric|min:1|max:5',
    //     ]);

    //     $userResponses = [];

    //     $feedbackResponses = $request->feedback_responses;

    //     foreach($feedbackResponses as $feedbackResponse) {
    //         $userResponses [] = [
    //             'course_id' => $request->course_id,
    //             'question_id' => $feedbackResponse['question_id'],
    //             'response' => $feedbackResponse['response'],
    //             'employee_id' => auth()->user()->id,
    //             'created_at' => time(),
    //         ];
    //     }

    //     UserResponse::insert($userResponses);

    //     UserComment::create([
    //         'course_id' => $request->course_id,
    //         'employee_id' => auth()->user()->id,
    //         'comment' => $request->comment,
    //     ]);

    //     //Redirect to certificate.
    //     return redirect()->route('admin.dashboard');
    // }

    // public function getFeedbackFormByCourse($id)
    // {
    //     $feedbackQuestions =
    // }
    // /**
    //  * Show the form for editing Category.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function edit($id)
    // {
    //     $teacher = User::findOrFail($id);
    //     $departments = Department::all();
    //     $positions = Position::all();
    //     return view('backend.employee.edit', compact('teacher','departments','positions'));
    // }


    // public function update(UpdateEmployeeRequest $request, $id)
    // {

    //     //        $request = $this->saveFiles($request);

    //     $teacher = User::findOrFail($id);
    //     //dd($teacher);
    //     $teacher->update($request->except('email'));
    //     if ($request->has('image')) {
    //         if($request->hasFile('image'))
    //         {

    //           $image = $request->file('image');
    //           //dd($image);
    //           //storing image name in a variable
    //           $image_name = time().'.'.$image->getClientOriginalExtension();

    //           $destinationPath = public_path('/uploads/employee');
    //           if($image->move($destinationPath, $image_name))
    //           {
    //             $teacher->avatar_location=$image_name;
    //           }

    //         }
    //         else
    //         {
    //             //$employee->avatar_location=$request->pictures;
    //         }
    //     }
    //     $teacher->active = isset($request->active)?1:0;
    //     $teacher->save();

    //     $data = [
    //        'department' => $request->department,
    //        'position' => $request->position
    //     ];
    //     $data_exits = DB::table('employee_profiles')->where('user_id',$id)->first();
    //     if($data_exits) {
    //         DB::table('employee_profiles')->where('user_id',$id)->update($data);
    //     } else {
    //         $data = [
    //             'user_id' => $id,
    //             'department' => $request->department,
    //             'position' => $request->position
    //         ];
    //         DB::table('employee_profiles')->insert($data);
    //     }



    //     return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    // }


    // /**
    //  * Display Category.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function show($id)
    // {
    //     $teacher = User::where('id',$id)->first();
    //     //dd($teacher);

    //     return view('backend.employee.show', compact('teacher'));
    // }


    // /**
    //  * Remove Category from storage.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    public function destroy($id)
    {
        $teacher = FeedbackQuestion::findOrFail($id);

        if ($teacher->count() > 0) {
            $teacher->delete();
            return redirect()->route('admin.feedback_question.index')->withFlashDanger(trans('alerts.backend.general.deleted'));
        } else {
            $teacher->delete();
        }

        return redirect()->route('admin.feedback_question.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    // /**
    //  * Delete all selected Category at once.
    //  *
    //  * @param Request $request
    //  */
    // public function massDestroy(Request $request)
    // {
    //     if ($request->input('ids')) {
    //         $entries = User::whereIn('id', $request->input('ids'))->get();

    //         foreach ($entries as $entry) {
    //             $entry->delete();
    //         }
    //     }
    // }


    // /**
    //  * Restore Category from storage.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function restore($id)
    // {
    //     $teacher = User::onlyTrashed()->findOrFail($id);
    //     $teacher->restore();

    //     return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.restored'));
    // }

    // /**
    //  * Permanently delete Category from storage.
    //  *
    //  * @param  int $id
    //  * @return \Illuminate\Http\Response
    //  */
    // public function perma_del($id)
    // {
    //     $teacher = User::onlyTrashed()->findOrFail($id);
    //     $teacher->teacherProfile->delete();
    //     $teacher->forceDelete();

    //     return redirect()->route('admin.employee.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    // }


    // /**
    //  * Update teacher status
    //  *
    //  * @param \Illuminate\Http\Request $request
    //  * @return \Illuminate\Http\Response
    //  **/
    // public function updateStatus()
    // {
    //     $teacher = User::find(request('id'));
    //     $teacher->active = $teacher->active == 1? 0 : 1;
    //     $teacher->save();
    // }


    // public function enrolled_student($course_id) {
    //     //dd($course_id);
    //     return view('backend.employee.enrolled_employee',['course_id'=>$course_id]);
    // }


    // public function enrolled_get_data(Request $request,$course_id,$show_deleted = 0) {
    //     //dd($show_deleted);
    //     $has_view = false;
    //     $has_delete = false;
    //     $has_edit = false;
    //     $teachers = "";


    //     if (request('show_deleted') == 1) {
    //         $teachers = User::query()->role('student')->onlyTrashed()->orderBy('created_at', 'desc');
    //     } else {
    //         $teachers = User::query()
    //                     ->select('users.*','course_student.created_at as enrolled_date')
    //                     ->role('student')
    //                     ->leftJoin('course_student','course_student.user_id','users.id')
    //                     ->where('course_student.course_id',$course_id)
    //                     ->orderBy('users.created_at', 'desc');
    //     }

    //     if (auth()->user()->isAdmin()) {
    //         $has_view = true;
    //         $has_edit = true;
    //         $has_delete = true;
    //     }
    //     //$teachers = $teachers->get();
    //     //dd($teachers);

    //     return DataTables::of($teachers)
    //         ->addIndexColumn()
    //         ->addColumn('actions', function ($q) use ($has_view, $has_edit, $has_delete,$course_id, $request) {
    //             $view = "";
    //             $edit = "";
    //             $delete = "";
    //             if ($request->show_deleted == 1) {
    //                 return view('backend.datatable.action-trashed')->with(['route_label' => 'admin.employee', 'label' => 'id', 'value' => $q->id]);
    //             }

    //             if ($has_view) {
    //                 /*
    //                 $view = view('backend.datatable.action-view')
    //                     ->with(['route' => route('admin.employee.course_detail', [$course_id,$q->id])])->render();
    //                 */

    //             }

    //             if ($has_edit) {

    //                 $edit =  view('backend.datatable.action-edit')
    //                         ->with(['route' => route('admin.employee.edit', ['id' => $q->id])])
    //                         ->render();
    //                 $view .= $edit;

    //             }

    //             if ($has_delete) {

    //                 $delete = view('backend.datatable.action-delete')
    //                     ->with(['route' => route('admin.employee.destroy', ['id' => $q->id])])
    //                     ->render();
    //                 $view .= $delete;

    //             }

    //             //$view .= '<a class="btn btn-warning mb-1" href="' . route('admin.courses.index', ['teacher_id' => $q->id]) . '">' . trans('labels.backend.courses.title') . '</a>';

    //             return $view;
    //         })

    //         ->addColumn('status', function ($q) {

    //              return ($q->active == 1) ? "Enabled" : "Disabled";
    //         })
    //         ->addColumn('track_employee', function ($q) use ($course_id, $request)  {

    //             return '<a class="btn btn-warning mb-1" href="'.route('admin.employee.course_detail', [$course_id,$q->id]).'">Track Employee</a>';
    //         })
    //         ->addColumn('enrolled_date', function ($q) {
    //             return ($q->enrolled_date) ? $q->enrolled_date : '-';
    //         })
    //         ->rawColumns(['actions', 'image', 'status','track_employee','enrolled_date'])
    //         ->make();

    // }
}
