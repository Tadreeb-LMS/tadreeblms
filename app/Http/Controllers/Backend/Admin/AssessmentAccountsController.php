<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssessmentAccountsRequest;
use App\Http\Requests\Admin\StoreAssignmentsRequest;
use App\Http\Requests\Admin\UpdateAssessmentAccountsRequest;
use App\Jobs\SendEmailJob;
use App\Models\AssessmentAccount;
use App\Models\Assignment;
use DB;
use Illuminate\Http\Request as HttpRequest;
// use Request;
use App\Models\Auth\User;
use App\Models\Category;
use App\Models\FeedbackQuestion;
use App\Models\CourseFeedback;
use App\Models\Course;
use App\Models\courseAssignment;
use App\Models\Department;
use App\Models\LearningPathway;
use App\Models\LearningPathwayCourse;
use App\Models\Stripe\SubscribeCourse;
use App\Models\UserLearningPathway;
use CustomHelper;
use Illuminate\Http\Request;
use Mail;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Carbon;
use App\Helpers\CustomValidation;
use App\Models\CourseAssignmentToUser;
use App\Models\courseInvitationAssignment;
use App\Models\CourseModuleWeightage;
use App\Models\Test;
use Illuminate\Support\Facades\DB as FacadesDB;
use Illuminate\Support\Facades\Validator;

class AssessmentAccountsController extends Controller
{

    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        
        $assessment_accounts = AssessmentAccount::where('deleted_at', NULL)->orderBy('created_at', 'desc')->get();
        return view('backend.assessment_accounts.index', compact('assessment_accounts'));
    }


    /**
     * Show the form for creating new Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        
        return view('backend.assessment_accounts.create');
    }

    /**
     * Store a newly created Category in storage.
     *
     * @param  \App\Http\Requests\StoreAssessmentAccountsRequest $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAssessmentAccountsRequest $request)
    {

        //dd($request->all());
        $code = random_strings(6);
        $i = 0;

        if ($request->teachers) {
            foreach ($request->teachers as $test) {
                $val = User::where('id', $test)->get();
            }
            $assessment_account = AssessmentAccount::create($request->all());
            if ($request->attachment) {

                if ($request->hasFile('attachment')) {
                    $image = $request->file('attachment');
                    $image_name = time() . '.' . $image->getClientOriginalExtension();

                    $destinationPath = public_path('/attachment/attachment');
                    if ($image->move($destinationPath, $image_name)) {
                        $assessment_account->attachment = $image_name;
                    }
                }
            }
            $assessment_account->code = $code;
            $assessment_account->assignment_id = $request->assignment_id;
            $assessment_account->trainees_id = $request->teachers;
            $assessment_account->first_name = $val[$i]->first_name;
            $assessment_account->last_name = $val[$i]->last_name;
            $assessment_account->email = $val[$i]->email;
            $assessment_account->phone = $val[$i]->phone;
            $assessment_account->save();

            //   CourseFeedback::where('course_id', $request->course_id)->delete();

            //   $courseFeedback = [];
            //   foreach($request->feedback_question_ids as $feedbackQuestion) {
            //       $courseFeedback[] = [
            //           'feedback_question_id' => $feedbackQuestion,
            //           'course_id' => $request->course_id,
            //           'created_by' => auth()->user()->id,
            //       ];
            //   }

            //   CourseFeedback::insert($courseFeedback);
        } else {
            $assessment_account = AssessmentAccount::create($request->all());
            if ($request->attachment) {

                if ($request->hasFile('attachment')) {
                    $image = $request->file('attachment');
                    $image_name = time() . '.' . $image->getClientOriginalExtension();

                    $destinationPath = public_path('/attachment/attachment');
                    if ($image->move($destinationPath, $image_name)) {
                        $assessment_account->attachment = $image_name;
                    }
                }
            }

            $assessment_account->code = $code;
            $assessment_account->assignment_id = $request->assignment_id;
            $assessment_account->course_id = $request->course_id;
            $assessment_account->save();

            // CourseFeedback::where('course_id', $request->course_id)->delete();

            //   $courseFeedback = [];
            //   dd
            //   foreach($request->feedback_question_ids as $feedbackQuestion) {
            //       $courseFeedback[] = [
            //           'feedback_question_id' => $feedbackQuestion,
            //           'course_id' => $request->course_id,
            //           'created_by' => auth()->user()->id,
            //       ];
            //   }

            //   CourseFeedback::insert($courseFeedback);
        }

        // return redirect()->route('admin.assessment_accounts.final-submit')->withFlashSuccess('Assessment created successfully. You are now last page');
        // return redirect()->route('admin.feedback.create_course_feedback')->withFlashSuccess('Assessment created successfully. You are now last page');
        return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully']);
    }


    /**
     * Show the form for editing Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $assessment = AssessmentAccount::findOrFail($id);
        return view('backend.assessment_accounts.edit', compact('assessment'));
    }

    /**
     * Update Category in storage.
     *
     * @param  \App\Http\Requests\UpdateAssessmentAccountsRequest $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAssessmentAccountsRequest $request, $id)
    {
        $assessment_account = AssessmentAccount::findOrFail($id);
        $assessment_account->update($request->except('email'));
        $assessment_account->active = isset($request->active) ? 1 : 0;
        $assessment_account->save();
        return redirect()->route('admin.assessment_accounts.index')->withFlashSuccess(trans('alerts.backend.general.updated'));
    }

    public function getCoursesByLanguage(Request $request)
    {
        $course_lang = $request->lang ?? 'english';

        $courses = Course::select('id','title')->where('deleted_at', '=', NULL)
                            ->where('course_lang', $course_lang)
                            ->get();
        return response()->json([
            'status' => 200,
            'courses' => $courses,
        ]);
    }

    public function getCoursesByCourseType(Request $request)
    {
        $course_type = $request->course_type ?? 'Offline';

        $courses = Course::select('id','title')->where('deleted_at', '=', NULL)
                            ->where('is_online', $course_type)
                            ->get();
        return response()->json([
            'status' => 200,
            'courses' => $courses,
        ]);
    }

    /**
     * Display Category.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $assessment = AssessmentAccount::findOrFail($id);
        return view('backend.assessment_accounts.show', compact('assessment'));
    }

    /**
     * Remove Category from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $assessment = AssessmentAccount::findOrFail($id);
        $assessment->delete();
        return redirect()->route('admin.assessment_accounts.index')->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }

    /**
     * Update assessment status
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     **/
    public function updateStatus()
    {
        $assessment = AssessmentAccount::find(request('id'));
        $assessment->active = $assessment->active == 1 ? 0 : 1;
        $assessment->save();
    }

    public function assignments(Request $request, $id = null, $type = null)
    {
        $user_id = $id;
        $count = Assignment::count();

        if ($type == 1) {
            $assessment_account = AssessmentAccount::findorFail($user_id);
            $assignments = DB::table('assignments')->select('assignments.*', 'tests.title')->leftjoin('tests', 'tests.id', '=', 'assignments.test_id')->where('assignments.deleted_at', NULL)
                ->join('assignment_questions', 'assignment_questions.assignment_id', '=', 'assignments.id')
                ->where('assignments.id', '=', $assessment_account->assignment_id);
            //->get();
        } else {
            $assignments = Assignment::join('tests', 'tests.id', '=', 'assignments.test_id')
                ->join('test_questions', 'test_questions.test_id', '=', 'tests.id')
                ->select('assignments.*', 'tests.title')
                ->where('assignments.deleted_at', NULL)
                ->distinct('tests')
                ->orderBy('id', 'DESC');
            // ->get();
        }
        if ($request->course_id != "") {
            $assignments = $assignments->where('assignments.course_id', (int)$request->course_id);
        }

        $assignments = $assignments->get();
        if ($request->course_id == "") {
            $assignments = [];
        }

        foreach ($assignments as $key => $value) {
            $value->total_marks = DB::table('assignment_questions')->select(DB::raw("SUM(test_questions.marks) as sum_marks"))->leftjoin('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')->where('assignment_questions.assignment_id', $value->id)->where('assignment_questions.assessment_account_id', '=', $user_id)->first()->sum_marks;
            $value->secured_marks = DB::table('assignment_questions')->select(DB::raw("SUM(marks) as sum_marks"))->where('assignment_questions.assignment_id', $value->id)->where('assignment_questions.assessment_account_id', '=', $user_id)->first()->sum_marks;
        }
        //dd($assignments);
        return view('backend.assessment_accounts.assignments', compact('assignments', 'user_id', 'type', 'count'));
    }

    public function assignment_question_answers(Request $request, $user_id, $assignment_id)
    {
        $assignment_questions = DB::table('assignment_questions')->select('assignment_questions.*', 'test_questions.question_type', 'test_questions.question_text', 'test_questions.solution')->leftjoin('test_questions', 'test_questions.id', '=', 'assignment_questions.question_id')->where('assignment_questions.assignment_id', $assignment_id)->where('assessment_account_id', $user_id)->get();
        foreach ($assignment_questions as $key => $value) {
            if ($value->question_type == 1 || $value->question_type == 2) {
                $value->options = DB::table('test_question_options')->select('test_question_options.*')->where('question_id', $value->question_id)->get();
            }
        }
        return view('backend.assessment_accounts.assessment_details', compact('assignment_questions', 'user_id', 'assignment_id'));
    }

    public function submit_result(HttpRequest $request)
    {
        $assignment_submit = json_decode($request->all_data);
        foreach ($assignment_submit as $key => $value) {
            $question = DB::table('assignment_questions')->where('id', "=", $value->question_id)->first();
            $test_question = DB::table('test_questions')->where('id', "=", $question->question_id)->first();
            DB::table('assignment_questions')->where('id', $value->question_id)->update(['is_correct' => $value->is_correct, 'marks' => $value->is_correct == 1 ? $test_question->marks : 0]);
        }
        return json_encode([
            'status' => 200,
            'message' => "Answers are corrected."
        ]);
    }

    public function question_solution(HttpRequest $request)
    {
        $question_id = $request->question_id;
        $question = DB::table('test_questions')->select('solution', 'marks', 'question_type')->where('id', $question_id)->first();
        return json_encode([
            'status' => 200,
            'message' => "Found the solution of the question",
            'details' => $question
        ]);
    }

    public function assignment_create(Request $request, $id = null)
    {
        //dd($request->all(), $id);
        //dd("after lesssions");
        $course_id = $request->course_id;
        $user_id = $id;

        if($course_id) {
            $tests = Test::where('course_id', $course_id)->where('published', 1)->get();
        } else {
            $tests = [];
        }
        
        $courses = Course::where('deleted_at', '=', NULL)->get();
        $category = Category::where('deleted_at', '=', NULL)->get();
        $teachers =  \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->get()->pluck('name', 'id');
        
        return view('backend.assessment_accounts.assignment_create', compact('user_id', 'tests', 'teachers', 'courses', 'course_id'));
    }

    public function assignment_create_without_course(Request $request, $id = null)
    {
        //dd($request->all(), $id);
        //dd("after lesssions");
        $course_id = $request->course_id;
        $user_id = $id;
        $tests = DB::table('tests')->where('course_id', $course_id)->where('deleted_at', '=', NULL)->get();
        $courses = Course::where('deleted_at', '=', NULL)->get();
        $category = Category::where('deleted_at', '=', NULL)->get();
        $teachers =  \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->get()->pluck('name', 'id');
        // return view('backend.assessment_accounts.create_assignment_course', compact('user_id', 'tests','teachers','courses','category'));
        return view('backend.assessment_accounts.assignment_create_without_course', compact('user_id', 'tests', 'teachers', 'courses', 'course_id'));
    }


    public function assignment_create_invitaion_course(Request $request, $id = null)
    {
        //dd($request->all());
        $reschedule = $request->reschudule ?? false;
        //dd($reschedule);
        $user_id = $id;
        $course_lang = $request->course_lang ?? 'english';
        
        $tests = DB::table('tests')->where('deleted_at', '=', NULL)->get();
        $courses = Course::where('deleted_at', '=', NULL)
                            ->where('course_lang', $course_lang)
                            ->whereIn('is_online', ['Offline'])
                            ->get();
        $category = Category::where('deleted_at', '=', NULL)->get();
        
        $teachers = User::query()->role('student')
                            ->whereIn('employee_type', ['internal'])
                            ->groupBy('email')
                            ->orderBy('created_at', 'desc')
                            ->active()
                            ->get()
                            ->pluck('name', 'id');

        $departments = Department::all();
        
        return view('backend.assessment_accounts.create__invitation_assignment_course', compact('user_id', 'tests', 'teachers', 'courses', 'category', 'departments','reschedule'));
        // return view('backend.assessment_accounts.assignment_create', compact('user_id', 'tests','teachers','courses'));
    }

    public function assignment_create_with_course(Request $request, $id = null)
    {
        
        $user_id = $id;
        $course_lang = $request->course_lang ?? 'english';
        
        $tests = DB::table('tests')->where('deleted_at', '=', NULL)->get();
        $courses = Course::where('deleted_at', '=', NULL)
                            ->where('course_lang', $course_lang)
                            ->get();
        $category = Category::where('deleted_at', '=', NULL)->get();
        
        $teachers = User::query()->role('student')
                            ->whereIn('employee_type', ['internal'])
                            ->groupBy('email')
                            ->orderBy('created_at', 'desc')
                            ->active()
                            ->get()
                            ->pluck('name', 'id');

        $departments = Department::all();
        
        return view('backend.assessment_accounts.create_assignment_course', compact('user_id', 'tests', 'teachers', 'courses', 'category', 'departments'));
        // return view('backend.assessment_accounts.assignment_create', compact('user_id', 'tests','teachers','courses'));
    }

    public function assignment_store(StoreAssignmentsRequest $request)
    {
        //dd($request->all());
        DB::beginTransaction();
        try {

            $url_code = random_strings(20);

            Assignment::where('course_id', $request->course_id)->delete();
            
            
            $assignment = new Assignment();
            $assignment->url_code = $url_code;
            $assignment->course_id = $request->course_id;
            $assignment->test_id = $request->test_id;
            $assignment->save();

            DB::commit();

            if(isset($request->add_test_to_course_only) && $request->add_test_to_course_only == 1) {
                return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully' , 'done' => true]);
            }
            
            return response()->json(['status' => 'success', 'clientmsg' => 'Added successfully', 'done' => false]);
        } catch( Exception $e ) {
            DB::rollBack();
            return response()->json(['status' => 'success', 'clientmsg' => $e->getMessage(), 'done' => false]);
        }
        
        
        
        
        
    }

    public function create_new_assisment()
    {
        $teachers =  \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->get()->pluck('name', 'id');
        // dd($teachers);
        $courses = Course::where('deleted_at', '=', NULL)->get();
        $departments = Department::all();
        $questions = FeedbackQuestion::get()->pluck('question', 'id');
        $category = Category::where('deleted_at', '=', NULL)->get();
        $assignment = Assignment::join('tests', 'tests.id', 'assignments.test_id')->select('assignments.*', 'tests.title')->where('assignments.deleted_at', Null)->whereNull('assignments.course_id')->get();
        // dd($assignment);
        return view('backend.assessment_accounts.add_new_account', compact('teachers', 'assignment', 'courses', 'category', 'questions', 'departments'));
    }

    public function final_submit(Request $request, $id = null)
    {
        $course = Course::find($id);
        return view('backend.assessment_accounts.final_submit', [
            'course_id' => $id ?? null,
            'course' => $course ?? null
        ]);
    }

    public function final_submit_store(Request $request)
    {
        $course_id = $request->course_id;

        if (!$course_id) {
            return redirect()
                ->back()
                ->withErrors(['course_id' => 'Invalid course'])
                ->withInput();
        }

        // ---------------- Base Validation ----------------
        $validator = Validator::make($request->all(), [
            'marks_required' => 'nullable|integer|min:1|max:100',
            'course_module_weight' => 'required|array',
            'course_module_weight.*' => 'nullable|numeric|min:0|max:100',
        ]);

        // ---------------- Custom Total Validation ----------------
        $validator->after(function ($validator) use ($request) {

            $weights = array_filter(
                $request->course_module_weight ?? [],
                fn ($value) => $value !== null && $value !== ''
            );

            $total = array_sum($weights);

            if ($total > 100) {
                $validator->errors()->add(
                    'course_module_weight',
                    'Total module weightage must not exceed 100%'
                );
            }

            // Uncomment if EXACTLY 100 is required
            /*
            if ($total !== 100) {
                $validator->errors()->add(
                    'course_module_weight',
                    'Total module weightage must be exactly 100%'
                );
            }
            */
        });

        // ---------------- Stop if validation fails ----------------
        if ($validator->fails()) {
            return redirect()
                ->back()
                ->withErrors($validator)
                ->withInput();
        }

        // ---------------- Save Data ----------------
        CourseModuleWeightage::updateOrCreate(
            ['course_id' => $course_id],
            [
                'weightage' => $request->course_module_weight,
                'minimun_qualify_marks' => $request->marks_required ?? 70,
            ]
        );

        Course::where('id', $course_id)->update([
            'current_step' => 'feedback-added',
            'published' => 1,
        ]);

        return redirect()
            ->route('admin.courses.index')
            ->withFlashSuccess('You completed all the flow for Courses...');
    }



    public function course_assignment(Request $request)
    {

        $this->validate($request, 
            [
                'title' => 'required',
                'course_id' => 'required',
                'due_date' => 'required',
                'teachers' => 'required_without:department_id',
                'department_id' => 'required_without:teachers',
            ],
            [
                'course_id.required' => 'Please select the course',
                'teachers.required_without' => 'Please select either a users or department',
                'department_id.required_without' => 'Please select either a users or department',
            ]
        );

        // course assignment
        $course_ids_arr = [];
        $single_course_id = $request->course_id;




        if ($single_course_id) {
            $course_ids_arr = [$single_course_id];
        }

        $course = Course::find($single_course_id);
        $course_link = url("/course/$course->slug");

        $users = [];
        $assign_to = null;

        if (isset($request->teachers)) {
            $assign_to = count($request->teachers) > 0 ? implode(',', $request->teachers) : null;
            $users = $request->teachers;
        } else {
            if ($request->department_id) {
                $dep_users = DB::table('employee_profiles')
                    ->leftJoin('department', 'department.id', 'employee_profiles.department')
                    ->where('department.id', '=', $request->department_id)
                    ->pluck('employee_profiles.user_id')->toArray();
                $users = $dep_users;
            }
        }


        //Chceck validation if already exits
        $already_course_assigned = CustomValidation::checkIfCourseIsAlreadyAssigned( $users, $course_ids_arr );

        if($already_course_assigned['status']) {
            return redirect()->route('admin.assessment_accounts.course-assign-list')->withFlashDanger($already_course_assigned['message']);
        }


        foreach ($course_ids_arr as $course_id) {
            $course_Ass = new courseAssignment;
            $course_Ass->title = $request->title;
            $course_Ass->course_id = $course_id;


            $course_Ass->assign_by = 1;
            $course_Ass->assign_date =  date('Y-m-d');
            $course_Ass->assign_to = $assign_to;
            //dd($course_Ass->assign_to);
            $course_Ass->due_date = $request->due_date;
            $course_Ass->message = $request->message;
            $course_Ass->department_id = $request->department_id;
            $course_Ass->save();

            if (isset($users) && (count($users) > 0)) {
                foreach ($users as $user) {
                    $emp = User::where('id', $user)->active()->first();
                    if (!$emp) {
                        continue;
                    }
    
                    CourseAssignmentToUser::updateOrCreate(
                        [
                            'course_assignment_id' => $course_Ass->id,
                            'course_id' => $course_Ass->course_id,
                            'user_id' => $user,
                        ],
                        [
                            'log_comment' => 'By Admin'
                        ]
                    );

                    $has_feedback = CourseFeedback::query()
                                    ->where('course_id', $course_Ass->course_id)
                                    ->count();
                                
                    //dd($has_feedback, $course_Ass->course_id);

                    $has_assesment = Assignment::query()
                                    ->where('course_id', $course_Ass->course_id)
                                    ->count();

                    SubscribeCourse::updateOrCreate([
                        'user_id' => $user,
                        'course_id' => $course_id,
                        
                    ],[
                        'has_feedback' => $has_feedback > 0 ? 1 : 0,
                        'has_assesment' => $has_assesment > 0 ? 1 : 0,
                        'course_trainer_name' => CustomHelper::getCourseTrainerName($course_id) ?? null,
                        'status' => 1,
                        'assign_date' => $course_Ass->assign_date,
                        'due_date' => $course_Ass->due_date
                    ]);

                    $user_fav_lang = $emp->fav_lang;
                    $username = $emp->full_name;
                    $course_name = $course->title;

                    if ($user_fav_lang == 'arabic') {
                        $username = $emp->arabic_full_name??$emp->full_name;
                        $course_name = $course->arabic_title??$course->title;
                    }

                    $variables = [
                        '{User_Name}' => $username,
                        '{Course_Name}' => $course_name,
                        '{Course_Link}' => $course_link,
                    ];

                    $email_template = CustomHelper::emailTemplates('course_assignment', $user_fav_lang, $variables);
                    
                    //$details['to_name'] = @$emp->full_name;
                    //$details['to_email'] = $emp->email;
                    //$details['subject'] = $email_template['subject'];
                    //$details['html'] = $email_template['email_content'];
    
                    //dispatch(new SendEmailJob($details));

                    $details = [
                        'to_email' => $emp->email,
                        'subject' => $email_template['subject'],
                        'html' => view('emails.default_email_template', [
                            'user' =>  $user,
                            'content' => $email_template
                        ])->render(), 
                    ];

                    dispatch(new SendEmailJob($details));
                }
            }
        }

        return redirect()->route('admin.assessment_accounts.course-assign-list')->withFlashSuccess(trans('Course assigned sucessfully...'));
    }



    public function course_assignment_invitation(Request $request)
    {

        $reschedule = $request->reschedule ?? false;

        $this->validate($request, 
            [
                'course_type' => 'required',
                'course_id' => 'required',
                'due_date' => 'required',
                'meeting_end_datetime' => 'required',
                'meeting_link' => 'required_without:classroom_location',
                'classroom_location' => 'required_without:meeting_link',
                'teachers' => 'required_without:department_id',
                'department_id' => 'required_without:teachers',
            ],
            [
                'course_id.required' => 'Please select the course',
                'teachers.required_without' => 'Please select either a users or department',
                'department_id.required_without' => 'Please select either a users or department',
                'meeting_link.required_without' => 'Please enter either a meeting link or classroom location',
                'classroom_location.required_without' => 'Please enter either a meeting link or classroom location',
            ]
        );

        // course assignment
        $course_ids_arr = [];
        $single_course_id = $request->course_id;

        if ($single_course_id) {
            $course_ids_arr = [$single_course_id];
        }

        $course = Course::find($single_course_id);
        $course_link = url("/course/$course->slug");

        $course_type = $request->course_type;

        if($course_type == 'Offline') {
            $location_link = 'using this link';
        } else {
            $location_link = 'at this location';
        }

        $users = [];
        $assign_to = null;

        if (isset($request->teachers)) {
            $assign_to = count($request->teachers) > 0 ? implode(',', $request->teachers) : null;
            $users = $request->teachers;
        } else {
            if ($request->department_id) {
                $dep_users = DB::table('employee_profiles')
                    ->leftJoin('department', 'department.id', 'employee_profiles.department')
                    ->where('department.id', '=', $request->department_id)
                    ->pluck('employee_profiles.user_id')->toArray();
                $users = $dep_users;
            }
        }


        //Chceck validation if already exits
        $already_course_assigned = CustomValidation::checkIfCourseIsAlreadyAssigned( $users, $course_ids_arr );

        //dd($already_course_assigned);

        if($already_course_assigned['status'] && $reschedule == 0) {
            return redirect()->route('admin.assessment_accounts.course-assign-list')->withFlashDanger($already_course_assigned['message']);
        } else if($already_course_assigned['status'] && $reschedule == 1) {
            // reschedule concept
            self::mappingCourseToUser($course_ids_arr, $request, $assign_to, $course, $location_link, $reschedule, $users);

            return redirect()->route('admin.assessment_accounts.course-invitation-list')->withFlashSuccess(trans('Course reassigned sucessfully...'));

        } else {

            self::mappingCourseToUser($course_ids_arr, $request, $assign_to, $course, $location_link, $reschedule, $users);

            return redirect()->route('admin.assessment_accounts.course-invitation-list')->withFlashSuccess(trans('Course assigned sucessfully...'));

        }

        return redirect()->route('admin.assessment_accounts.course-invitation-list')->withFlashSuccess(trans('Course assigned sucessfully...'));

        
    }


    public function mappingCourseToUser($course_ids_arr = [], $request, $assign_to, $course, $location_link, $reschedule = false, $users)
    {
        foreach ($course_ids_arr as $course_id) {
            $course_Ass = new courseAssignment;
            $course_Ass->title = $request->title;
            $course_Ass->course_id = $course_id;

            

            $course_Ass->assign_by = 1;
            $course_Ass->assign_date = date('Y-m-d');
            $course_Ass->assign_to = $assign_to;
            //dd($course_Ass->assign_to);
            $course_Ass->due_date = $request->due_date ? Carbon::parse($request->due_date)->format('Y-m-d H:i:s') : null;
            $course_Ass->meeting_end_datetime = $request->meeting_end_datetime ? Carbon::parse($request->meeting_end_datetime)->format('Y-m-d H:i:s') : null;
            $course_Ass->message = $request->message;
            $course_Ass->department_id = $request->department_id;
            $course_Ass->by_invitation = 1;
            $course_Ass->reschedule = $reschedule;
            $course_Ass->classroom_location = $request->classroom_location ?? null;
            $course_Ass->meeting_link = $request->meeting_link ?? null;
            $course_Ass->save();

            //dd($course_Ass);

            $meet_link = null;
            if(!empty($request->classroom_location)) {
                $meet_link = $request->classroom_location;
            } else {
               $meet_link = $request->meeting_link ?? null; 
            }

            if (isset($users) && (count($users) > 0)) {
                foreach ($users as $user) {
                    $emp = User::where('id', $user)->active()->first();
                    //dd($emp);
                    if (!$emp) {
                        continue;
                    }

                    $sb = SubscribeCourse::where([
                        'user_id' => $user,
                        'course_id' => $course_id,
                    ])->first();

                    
                    if ($reschedule && $sb && $sb->is_attended == 1) {
                        continue;    
                    }
                    
    
                    CourseAssignmentToUser::updateOrCreate(
                        [
                            //'course_assignment_id' => $course_Ass->id,
                            'course_id' => $course_Ass->course_id,
                            'user_id' => $user,
                        ],
                        [
                            'course_assignment_id' => $course_Ass->id,
                            'log_comment' => 'By Admin',
                            'by_invitation' => '1'
                        ]
                    );



                    $has_feedback = CourseFeedback::query()
                                    ->where('course_id', $course_Ass->course_id)
                                    ->count();

                    //dd($has_feedback);

                    $has_assesment = Assignment::query()
                                    ->where('course_id', $course_Ass->course_id)
                                    ->count();

                    $sb = SubscribeCourse::updateOrCreate([
                        'user_id' => $user,
                        'course_id' => $course_id,
                        
                    ],[
                        'has_feedback' => $has_feedback > 0 ? 1 : 0,
                        'has_assesment' => $has_assesment > 0 ? 1 : 0,
                        'course_trainer_name' => CustomHelper::getCourseTrainerName($course_id) ?? null,
                        'status' => 1,
                        'assign_date' => $course_Ass->assign_date,
                        'due_date' => $course_Ass->due_date,
                        'by_invitation' => 1
                    ]);

                    //dd($sb, $course_Ass->due_date);

                    $user_fav_lang = $emp->fav_lang;
                    $username = $emp->full_name;
                    $course_name = $course->title;

                    if ($user_fav_lang == 'arabic') {
                        $username = $emp->arabic_full_name??$emp->full_name;
                        $course_name = $course->arabic_title??$course->title;
                    }

                    $variables = [
                        '{Course_Date}' => Carbon::parse($course_Ass->due_date)->format('Y-m-d'),
                        '{Course_Time}' => Carbon::parse($course_Ass->due_date)->format('H:i:s'),
                        '{User_Name}' => $username,
                        '{Course_Name}' => $course_name,
                        '{Meet_Link}' => $meet_link,
                        '{location_link}' => $location_link,
                    ];

                    $email_template = CustomHelper::emailTemplates('invitation_assignment', $user_fav_lang, $variables);
                    
                    //$details['to_name'] = @$emp->full_name;
                    //$details['to_email'] = $emp->email;
                    //$details['subject'] = $email_template['subject'];
                    //$details['html'] = $email_template['email_content'];
    
                    //dispatch(new SendEmailJob($details));

                    $details = [
                        'to_email' => $emp->email,
                        'subject' => $email_template['subject'],
                        'html' => view('emails.default_email_template', [
                            'user' =>  $user,
                            'content' => $email_template
                        ])->render(), 
                    ];

                    dispatch(new SendEmailJob($details));
                }
            }
        }
    }



    public function course_assignment_update(Request $request)
    {

        DB::table('course_assignment')->where('id', $request->user_id)->update([
            'title' => $request->title,
            'due_date' => $request->due_date,
            'assign_to' => isset($request->teachers) ? count($request->teachers) > 0 ? implode(',', $request->teachers) : null : null,
            'message' => $request->message,
            'department_id' => $request->department_id,
            'course_id' => $request->course_id,
        ]);

        if (isset($request->teachers) && (count($request->teachers) > 0)) {

            DB::table('subscribe_courses')->where('course_id', $request->course_id)->delete();

            foreach ($request->teachers as $teacher) {
                DB::table('subscribe_courses')->insert([
                    'user_id' => $teacher,
                    'course_id' => $request->course_id,
                    'status' => 1,
                    'assign_date' => date('Y-m-d H:i:s'),
                    'due_date' => $request->due_date,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
        }
        return redirect()->route('admin.assessment_accounts.course-assign-list')->withFlashSuccess(trans('Course Assignment Updated sucessfully...'));
    }


    public function course_assign_edit($id, Request $request)
    {
        $user_id = $id;
        $assessment = DB::table('course_assignment')->where('id', $id)->first();
        $tests = DB::table('tests')->where('deleted_at', '=', NULL)->get();
        $courses = Course::where('deleted_at', '=', NULL)->get();
        $category = Category::where('deleted_at', '=', NULL)->get();
        $teachers =  \App\Models\Auth\User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->get()->pluck('name', 'id');

        //echo '<pre>';print_r($teachers);die;
        $departments = Department::all();

        return view('backend.assessment_accounts.course_assign_edit', compact('user_id', 'tests', 'teachers', 'courses', 'category', 'departments', 'assessment'));
    }

    public function course_assign_list_(Request $request)
    {
        if ($request->ajax()) {
            $assessments = courseAssignment::with('assignedBy', 'course', 'department','hasCourse')
                            ->whereHas('hasCourse')
                            ->where('by_invitation','0')
                            //->groupBy('course_id')
                            ->orderBy('id', 'Desc');
            

            return DataTables::of($assessments)
                ->addColumn('course_title', function ($row) {
                    return @$row->course->title;
                })
                ->editColumn('assign_date', function ($row) {
                    return @$row->assign_date != "" ? Carbon::parse($row->assign_date)->format('d/m/Y') : '-';
                })
                ->editColumn('due_date', function ($row) {
                    return @$row->due_date != "" ? Carbon::parse($row->due_date)->format('d/m/Y') : '-';
                })
                ->addColumn('course_cat', function ($row) {
                    return @$row->course->category->name;
                })
                ->addColumn('course_code', function ($row) {
                    return @$row->course->course_code;
                })
                ->addColumn('assign_by', function ($row) {
                    return @$row->assignedBy->full_name;
                })
                ->addColumn('deprt_title', function ($row) {
                    return @$row->department->title;
                })
                ->addColumn('assigned_user_names', function ($row) {
                    return @$row->assigned_user_names;
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = $request->input('search.value');
                        
                        $query->where(function ($query) use ($search) {
                            $query->where('title', 'like', "%{$search}%")
                                ->whereHas('hasCourse')
                                ->orWhereHas('course', function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%")
                                        ->orWhere('course_code', 'like', "%{$search}%");
                                })
                                ->orWhereHas('course.category', function ($query) use ($search) {
                                    $query->where('name', 'like', "%{$search}%");
                                })
                                ->orWhereHas('assignedBy', function ($query) use ($search) {
                                    $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                                })
                                ->orWhereHas('department', function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%");
                                })
                                ->orWhereRaw("
                                EXISTS (
                                    SELECT 1
                                    FROM users
                                    WHERE FIND_IN_SET(users.id, course_assignment.assign_to)
                                    AND (
                                        CONCAT(users.first_name, ' ', users.last_name) LIKE ?
                                        OR users.email LIKE ?
                                    )
                                )
                            ", ["%{$search}%", "%{$search}%"]);
                        });
                    }
                })
                ->make();
        }

        $internal_users = User::query()
            ->where('employee_type', 'internal')
            //->where('active','1')
            ->get();

        $published_courses = Course::query()
            //->where('published', '1')
            ->get();

        return view('backend.assessment_accounts.course_assignment_index',[
            'internal_users' => $internal_users,
            'published_courses' => $published_courses,
        ]);
    }

    public function course_assign_list(Request $request)
    {
        //dd($request->all());
        if ($request->ajax()) {

            $user_id = $request->user_id ?? null;
            $course_id = $request->course_id ?? null;

            $assessments = CourseAssignmentToUser::query()
                            ->with('course','assignment','user')
                            ->where('by_pathway','0')
                            ->when(!empty($course_id), function ($q) use ($course_id) {
                                $q->where('course_id', $course_id);
                            })
                            ->when(!empty($user_id), function ($q) use ($user_id) {
                                $q->where('user_id', $user_id);
                            })
                            ->orderBy('id', 'Desc');
            

            return DataTables::of($assessments)
                ->addColumn('course_title', function ($row) {
                    if($row->course) {
                        return @$row->course->title;
                    } else {
                        return '';
                    }
                })
                ->addColumn('title', function ($row) {
                    if($row->assignment) {
                        return @$row->assignment->title;
                    } else {
                        return '';
                    }
                })
                ->editColumn('assign_date', function ($row) {
                    return @$row->assignment->assign_date != "" ? Carbon::parse($row->assignment->assign_date)->format('d/m/Y') : '-';
                })
                ->editColumn('due_date', function ($row) {
                    return @$row->assignment->due_date != "" ? Carbon::parse($row->assignment->due_date)->format('d/m/Y') : '-';
                })
                ->addColumn('course_cat', function ($row) {
                    return @$row->course->category->name;
                })
                ->addColumn('course_code', function ($row) {
                    return @$row->course->course_code;
                })
                ->addColumn('assign_by', function ($row) {
                    return @$row->assignment->assignedBy->full_name;
                })
                ->addColumn('deprt_title', function ($row) {
                    return '';
                })
                ->addColumn('assignment_title', function ($row) {
                    if($row->assignment) {
                        return @$row->assignment->title;
                    } else {
                        return '';
                    }
                })
                ->addColumn('assigned_user_names', function ($row) {
                    return @$row->user->full_name;
                })
                ->filter(function ($query) use ($request) {
                    $search = $request->input('search')['value'] ?? null;

                    if (!empty($search)) {
                        $query->where(function($q) use ($search) {
                            // Search by user name (first + last)
                            $q->whereHas('user', function ($uq) use ($search) {
                                $uq->where('first_name', 'like', "%{$search}%")
                                ->orWhere('last_name', 'like', "%{$search}%")
                                ->orWhereRaw("CONCAT(first_name, ' ', last_name) LIKE ?", ["%{$search}%"]);
                            });
                            $q->orWhereHas('course', function ($query) use ($search) {
                                    $query->where('title', 'like', "%{$search}%")
                                        ->orWhere('course_code', 'like', "%{$search}%");
                            });
                            $q->orWhereHas('course.category', function ($query) use ($search) {
                                $query->where('name', 'like', "%{$search}%");
                            });

                            $q->orWhereHas('assignment', function ($aq) use ($search) {
                                $aq->where('title', 'like', "%{$search}%");
                            });

                        });
                    }
                })

                ->make();
        }

        $internal_users = User::query()
            ->where('employee_type', 'internal')
            //->where('active','1')
            ->get();

        $published_courses = Course::query()
            //->where('published', '1')
            ->get();

            

        return view('backend.assessment_accounts.course_assignment_index',[
            'internal_users' => $internal_users,
            'published_courses' => $published_courses,
        ]);
    }

    public function course_invitation_list(Request $request)
    {
        if ($request->ajax()) {

            $user_id = $request->user_id ?? null;
            $course_id = $request->course_id ?? null;

            $assessments = CourseAssignmentToUser::query()
                            ->with('course','assignment','user')
                            ->when(!empty($course_id), function ($q) use ($course_id) {
                                $q->where('course_id', $course_id);
                            })
                            ->when(!empty($user_id), function ($q) use ($user_id) {
                                $q->where('user_id', $user_id);
                            })
                            ->where('by_invitation','1')
                            //->groupBy('course_id')
                            ->orderBy('id', 'Desc');
            

            return DataTables::of($assessments)
                ->addColumn('course_title', function ($row) {
                    return @$row->course->title;
                })
                ->editColumn('assign_date', function ($row) {
                    return @$row->assignment->assign_date != "" ? Carbon::parse($row->assignment->assign_date)->format('d/m/Y') : '-';
                })
                ->editColumn('due_date', function ($row) {
                    return @$row->assignment->due_date != "" ? Carbon::parse($row->assignment->due_date)->format('d/m/Y H:i:s') : '-';
                })
                ->editColumn('meeting_end_datetime', function ($row) {
                    return @$row->assignment->meeting_end_datetime != "" ? Carbon::parse($row->assignment->meeting_end_datetime)->format('d/m/Y H:i:s') : '-';
                })
                ->addColumn('course_cat', function ($row) {
                    return @$row->course->category->name;
                })
                ->addColumn('course_code', function ($row) {
                    return @$row->course->course_code;
                })
                ->addColumn('assign_by', function ($row) {
                    return @$row->assignment->assignedBy->full_name;
                })
                ->addColumn('deprt_title', function ($row) {
                    return @$row->department->title;
                })
                ->addColumn('assigned_user_names', function ($row) {
                    return @$row->user->full_name;
                })
                // ->addColumn('assignment_title', function ($row) {
                //     if($row->assignment) {
                //         return @$row->assignment->title;
                //     } else {
                //         return '';
                //     }
                // })
                ->filter(function ($query) use ($request) {
                    // if ($request->has('search') && !empty($request->input('search.value'))) {
                    //     $search = $request->input('search.value');
                        
                    //     $query->where(function ($query) use ($search) {
                    //         $query->where('title', 'like', "%{$search}%")
                    //             ->whereHas('hasCourse')
                    //             ->orWhereHas('course', function ($query) use ($search) {
                    //                 $query->where('title', 'like', "%{$search}%")
                    //                     ->orWhere('course_code', 'like', "%{$search}%");
                    //             })
                    //             ->orWhereHas('course.category', function ($query) use ($search) {
                    //                 $query->where('name', 'like', "%{$search}%");
                    //             })
                    //             ->orWhereHas('assignedBy', function ($query) use ($search) {
                    //                 $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                    //             })
                    //             ->orWhereHas('department', function ($query) use ($search) {
                    //                 $query->where('title', 'like', "%{$search}%");
                    //             })
                    //             ->orWhereRaw("
                    //             EXISTS (
                    //                 SELECT 1
                    //                 FROM users
                    //                 WHERE FIND_IN_SET(users.id, course_assignment.assign_to)
                    //                 AND (
                    //                     CONCAT(users.first_name, ' ', users.last_name) LIKE ?
                    //                     OR users.email LIKE ?
                    //                 )
                    //             )
                    //         ", ["%{$search}%", "%{$search}%"]);
                    //     });
                    // }
                })
                ->make();
        }

        $internal_users = User::query()
            ->where('employee_type', 'internal')
            //->where('active','1')
            ->get();

        $published_courses = Course::query()
            //->where('published', '1')
            ->get();

        return view('backend.assessment_accounts.course_invitation_assignment_index',[
            'internal_users' => $internal_users,
            'published_courses' => $published_courses,
        ]);
    }

    public function course_assign_delete($id)
    {
        $course_assignment = DB::table('course_assignment')->where('id', $id)->first();
        DB::table('subscribe_courses')->where('course_id', $course_assignment->course_id)->delete();
        DB::table('course_assignment')->where('id', $id)->delete();

        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
        print_r($id);
        die;
    }

    public function assignments_delete($id)
    {
        DB::table('assignments')->where('id', $id)->delete();
        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
    }
}
