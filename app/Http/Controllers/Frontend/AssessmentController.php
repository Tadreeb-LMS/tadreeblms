<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAssessmentAccountsRequest;
use App\Models\AssessmentAccount;
use App\Models\Assignment;
use App\Models\AssignmentQuestion;
use App\Models\Course;
use App\Models\courseAssignment;
use App\Models\CourseFeedback;
use App\Models\Lesson;
use App\Models\ManualAssessment;
use App\Models\Stripe\SubscribeCourse;
use App\Models\TestQuestionOption;
use DB;
use Illuminate\Http\Request;
use Session;
use Auth;
use Carbon\Carbon;
use CustomHelper;
use Exception;

class AssessmentController extends Controller
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

    public function courseFeedback(Request $request)
    {
        $course_id = $request->course_id;
        
        // Use Eloquent with eager loading for better performance and security
        $courses_feedbacks = CourseFeedback::where('course_id', $course_id)
            ->with(['feedback.feedbackOptions']) // Eager load feedback questions and their options
            ->get();

        return view($this->path . '/assignment/' . 'user-feedback', compact('course_id', 'courses_feedbacks'));
    }

    public function index(Request $request)
    {
        
        //check auth
        if (!Auth::check()) {
            return redirect('/');
        }
        
        if ($request->assignment) {
            $assignment_code = $request->assignment;
            $id = $request->id;
            $logged_in_user_id = auth()->user()->id;
            
            if(empty(auth()->user()->employee_type)) {
                // admin
                $assignment = Assignment::where('url_code', $assignment_code)->first();
                //dd($assignment);
            } else {
                $request->session()->put('assessment_assignment_id', $id);
                $request->session()->put('assessment_test_id', $request->assessment_id);
                $user_id = $logged_in_user_id;
                $assignment = Assignment::where('url_code', $assignment_code)->first();
                $assignment_id = $assignment->id;


                $assignment = Assignment::where('id', $assignment_id)->first();
                //dd($user_id, $assignment_id, $assignment);
                if ($assignment == null) {
                    Session::flash('message', 'Assignment Not Found!');
                    Session::flash('alert-class', 'alert-danger');
                    return redirect()->route('online_assessment', 'assignment=' . $assignment->url_code)->withFlashSuccess("Assignment Not Found.");
                }

                $assessment_account = courseAssignment::where('id', $id)->first();
                

                if ($assessment_account == null) {
                    //throw new Exception('Assignment is not valid!');
                    return redirect()->route('user.mycourses');
                }
            }

            

            $test_questions = DB::table('test_questions')->select('test_questions.*', 'tests.title')
                ->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')
                ->where('test_questions.is_deleted', '=', 0)
                ->whereNull('test_questions.deleted_at')
                ->where('test_questions.test_id', $assignment->test_id)
                ->where('tests.course_id', $assignment->course_id)
                //->inRandomOrder()
                ->get();

            //dd($test_questions);

            //dd($assignment->test_id);
            foreach ($test_questions as $key => $value) {

                if (in_array($value->question_type, [1, 2])) {
                    //  echo '<pre>'; print_r($test_questions);die;
                    $options = DB::table('test_question_options')
                    ->select('test_question_options.*')
                    ->where('test_question_options.question_id', $value->id)
                    ->get();
                    //dd($options);

                    if(!empty($value->option_json)) {
                        $decoded_string = html_entity_decode($value->option_json);
                        preg_match('/<p>(\[\[.*?\]\])<\/p>/', $decoded_string, $matches);
                        if (isset($matches[1])) {
                            
                            $dataArray = json_decode($matches[1], true);
                            if( count($dataArray) ) {
                                
                                foreach ($dataArray as $this_op) {
                                    
                                    // DB::table('test_question_options')->insert([
                                    //     'question_id' => $value->id,
                                    //     'option_text' => $this_op[0],
                                    //     'is_right' => $this_op[1]
                                    // ]);

                                    TestQuestionOption::updateOrInsert(
                                        ['question_id' => $value->id, 'option_text' => $this_op[0]],
                                        [
                                            'question_id' => $value->id, 
                                            'option_text' =>$this_op[0], 
                                            'is_right' =>  $this_op[1]
                                        ]
                                    );
                                }

                                $options = DB::table('test_question_options')->select('test_question_options.*')->where('test_question_options.question_id', $value->id)->get();

                                
                            }
                        } 
                    }
 
                    $value->options = $options;
                }
            }

            if(empty(auth()->user()->employee_type)) {
                return view($this->path . '/assignment/' . 'question_set', compact('test_questions', 'assignment'));
            } else {
                return view($this->path . '/assignment/' . 'question_set', compact('test_questions', 'assignment', 'assessment_account'));
            }



        } else {
            $test_questions = [];
            $assessment_account = [];
            $assignment = [];
            return view($this->path . '/assignment/' . 'question_set', compact('test_questions', 'assignment', 'assessment_account'));
            return redirect('');
        }
    }

    public function manualOnlineAssessment(Request $request)
    {
        $assessment_account = [];
        $manual_assessment_id = $request->manual_assessment_id;
        $ma = ManualAssessment::find($manual_assessment_id);
        $date = Carbon::parse($ma->due_date); // Replace with your date
        if ($date->isPast()) {
            return abort('403', "Due date is passed. Please contact admin.");
        }
        $assignment_code = $request->assignment;
        $id = $request->id;
        $logged_in_user_id = auth()->user()->id;
        $request->session()->put('assessment_assignment_id', $id);
        $request->session()->put('assessment_test_id', $request->assessment_id);
        //dd($request->assignment, $logged_in_user_id);
        $user_id = $logged_in_user_id;
        $assignment = Assignment::where('url_code', $assignment_code)->first();
        $assignment_id = $assignment->id;

        $assignment = Assignment::where('id', $assignment_id)->firstOrFail();

        $test_questions = DB::table('test_questions')->select('test_questions.*', 'tests.title')
            ->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')
            ->where('test_questions.is_deleted', '=', 0)
            ->whereNull('test_questions.deleted_at')
            ->where('test_questions.test_id', $assignment->test_id)
            ->inRandomOrder()
            ->get();

        foreach ($test_questions as $key => $value) {

            if (in_array($value->question_type, [1, 2])) {
                //  echo '<pre>'; print_r($test_questions);die;
                $options = DB::table('test_question_options')->select('test_question_options.*')->where('test_question_options.question_id', $value->id)->get();
                if ((count($options) == 0) && (!empty($value->option_json))) {
                    foreach (json_decode($value->option_json) as $this_op) {
                        // DB::table('test_question_options')->insert([
                        //     'question_id' => $value->id,
                        //     'option_text' => $this_op[0],
                        //     'is_right' => $this_op[1]
                        // ]);
                        TestQuestionOption::updateOrInsert(
                            ['question_id' => $value->id, 'option_text' => $this_op[0]],
                            [
                                'question_id' => $value->id, 
                                'option_text' =>$this_op[0], 
                                'is_right' =>  $this_op[1]
                            ]
                        );
                    }
                    $options = DB::table('test_question_options')->select('test_question_options.*')->where('test_question_options.question_id', $value->id)->get();
                }
                $value->options = $options;
            }
        }

        return view($this->path . '/assignment/' . 'manual_question_set', compact('test_questions', 'assignment', 'assessment_account'));
    }

    public function verify(Request $request)
    {
        $url_code = $request->url_code;
        $verification_code = $request->verification_code;
        $assignment = Assignment::where('url_code', $url_code)->where('verify_code', $verification_code)->first();
        if ($assignment == null) {
            Session::flash('message', 'Assignment Not Found!');
            Session::flash('alert-class', 'alert-danger');
            return redirect()->route('online_assessment', 'assignment=' . $request->url_code)->withFlashSuccess("Assignment Not Found.");
        }

        return view($this->path . '/assignment/' . 'user_details', compact('assignment'));
    }

    public function verifyDetails(Request $request)
    {
        $urlVal = AssessmentAccount::join('assignments', 'assignments.id', '=', 'assessment_accounts.assignment_id')
            ->where('email', $request->email)
            ->first();
        $url_code = $urlVal->url_code;
        $verification_code = $urlVal->verification_code;
        $id = $urlVal->id;
        $assignment = Assignment::where('url_code', $url_code)->where('verify_code', $verification_code)->first();
        return view($this->path . '/assignment/' . 'user_details_code', compact('assignment', 'urlVal'));
    }

    public function question_set(Request $request)
    {
        if ($request->session()->get('assessment_user_id') && $request->session()->get('assessment_assignment_id')) {
            $user_id = $request->session()->get('assessment_user_id');
            $assignment_id = $request->session()->get('assessment_assignment_id');

            $assignment = Assignment::where('id', $assignment_id)->first();
            //dd($user_id, $assignment_id, $assignment);
            if ($assignment == null) {
                Session::flash('message', 'Assignment Not Found!');
                Session::flash('alert-class', 'alert-danger');
                return redirect()->route('online_assessment', 'assignment=' . $assignment->url_code)->withFlashSuccess("Assignment Not Found.");
            }

            $assessment_account = AssessmentAccount::where('id', $user_id)->where('assignment_id', $assignment_id)->first();
            if ($assessment_account == null) {
                Session::flash('message', 'Assignment is not valid!');
                Session::flash('alert-class', 'alert-danger');
                return redirect()->route('online_assessment', 'assignment=' . $assignment->url_code)->withFlashSuccess("Assignment is not valid.");
            }

            $test_questions = DB::table('test_questions')->select('test_questions.*', 'tests.title')->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')
            ->where('test_questions.is_deleted', '=', 0)
            ->whereNull('test_questions.deleted_at')
            ->where('test_questions.test_id', $assignment->test_id)->inRandomOrder()->limit($assignment->total_question)->get();

            //dd($assignment->test_id);
            foreach ($test_questions as $key => $value) {
                if (in_array($value->question_type, [1, 2])) {
                    $value->options = DB::table('test_question_options')->select('test_question_options.*')->where('test_question_options.question_id', $value->id)->get();
                }
            }
            // echo "<pre>";
            // print_r($test_questions);
            // exit();
            return view($this->path . '/assignment/' . 'question_set', compact('test_questions', 'assignment', 'assessment_account'));
        } else {
            return redirect('');
        }
    }

    public function assignment_test_elapsed_time(Request $request)
    {
        $user_id = $request->session()->get('assessment_user_id');
        $assignment_id = $request->session()->get('assessment_assignment_id');

        $assessment_account = AssessmentAccount::where('id', $user_id)->where('assignment_id', $assignment_id)->first();
        // $assessment_account->elapsed_time = (int)$request->elapsed_time + (int)$assessment_account->elapsed_time;
        $assessment_account->elapsed_time = (int)$request->elapsed_time;
        $assessment_account->save();
        return json_encode(array(
            'status' => 200,
            'message' => 'Elapsed Time Updated.'
        ));
    }

    public function assignment_test_report(Request $request)
    {
        echo "sdsd";
        exit();
    }

    public function answer_submit(Request $request)
    {
        $user_id = auth()->user()->id;
        $assignment_id = $request->session()->get('assessment_assignment_id');
        $assessment_test_id = $request->session()->get('assessment_test_id');
        //dd($user_id, $assignment_id, $assessment_test_id); // 4063, 1351, 9
        $all_assignment_answers = json_decode($request->all_data);

        $aq = AssignmentQuestion::where('assignment_id', $assignment_id)->where('assessment_account_id', $user_id)->first();

        $attempt = $aq ? $aq->attempt + 1 : 1;

        //dd($all_assignment_answers);

        foreach ($all_assignment_answers as $key => $value) {
            $question = DB::table('test_questions')->where('id', "=", $value->question_id)->first();

            if(empty($value->answer)) {
                continue;
            }

            $answer_text = null;
            if($value->question_type == 1) {
                $answer_text = $value->answer;
                if($value->answer) {
                    $anser = TestQuestionOption::where('id',$value->answer)->first();
                    $answer_text =  $anser->option_text;
                } 
                
            } else {
                $answer_text = $value->answer;
            }

            $data = array(
                'assignment_id' => $assignment_id,
                'assessment_test_id' => $assessment_test_id,
                'assessment_account_id' => $user_id,
                'question_id' => $value->question_id,
                'answer' => $value->answer,
                'answer_text' => $answer_text
            );

            $is_correct = 0;
            if ($question->question_type == 2) {
                $correct_options = DB::table('test_question_options')->where('question_id', "=", $value->question_id)->where('is_right', '=', 1)->get();
                $correct_option_ids = [];
                foreach ($correct_options as $o_key => $o_value) {
                    array_push($correct_option_ids, $o_value->id);
                }
                $student_answer = json_decode($value->answer);
                sort($correct_option_ids);
                sort($student_answer);
                if ($correct_option_ids == $student_answer) {
                    $is_correct = 1;
                } else {
                    $is_correct = 2;
                }
            } else if ($question->question_type == 1) {
                $correct_option = DB::table('test_question_options')->where('question_id', "=", $value->question_id)->where('is_right', '=', 1)->first();
                $student_answer = $value->answer;
                if ($correct_option->id == $student_answer) {
                    $is_correct = 1;
                } else {
                    $is_correct = 2;
                }
            }
            $data['attempt'] = $attempt;
            $data['marks'] = $is_correct == 1 ? $question->marks : 0;
            $data['is_correct'] = $is_correct;
            //dd($data);
            AssignmentQuestion::insert($data);
        }


        //Update the subscribe Course
        if(isset($user_id) && isset( $assignment_id )) {

            $ass_data = Assignment::query()
                    ->where('id', $assessment_test_id)
                    ->first();

            //dd($ass_data, $assessment_test_id);

            $course_id = $ass_data->course_id ?? null;

            if($course_id) {

                $course_progress_status = 1;

                $has_feedback = CourseFeedback::query()
                                    ->where('course_id', $course_id)
                                    ->count();

                if($has_feedback == 0) {
                   $course_progress_status = 2; 
                }

                $sb = SubscribeCourse::with('course')->where('course_id', $course_id)
                            ->where('user_id', $user_id)->first();

                

                if($sb) {
                    $sb->update([
                        'has_assesment' => 1,
                        'assesment_taken' => true,
                        'course_progress_status' => $course_progress_status
                    ]);

                    $first_lesson = Lesson::where('course_id', $sb->course->id)->first();

                    $has_feedback = $sb->has_feedback;


                    if($first_lesson) {
                        if($has_feedback == 0) {
                            $return_url = route('lessons.show',[$sb->course->id,$first_lesson->slug]);
                        } else {
                            $return_url = route('lessons.show',[$sb->course->id,$first_lesson->slug]);
                        }

                         //dd(route('lessons.show',[$sb->course->id,$first_lesson->slug]));

                    } else {
                        if($has_feedback == 0) {
                            $return_url = route('courses.show',[$sb->course->slug]);
                        } else {
                            $return_url = route('courses.show',[$sb->course->slug]);
                        }
                        

                    }

                   
                }           
                

                $progressdata = CustomHelper::updateUserProgress($user_id, $course_id);
            }
          
            
        }
        
        

        $request->session()->forget('assessment_user_id');
        $request->session()->forget('assessment_assignment_id');
        return json_encode(array(
            'status' => 200,
            'has_feedback' => $has_feedback,
            'return_url' => $return_url,
            'message' => 'Thank you for attending this assessment. We will get back to you with the result soon.'
        ));
    }







    public function feedback_submit(Request $request)
    {
        //dd("joj");
        $user_id = auth()->user()->id;
        $course_id = $request->course_id;

        $all_assignment_answers = json_decode($request->all_data);
        //dd($all_assignment_answers);
        foreach ($all_assignment_answers as $key => $value) {

            $data = array(
                'user_id' => $user_id,
                'course_id' => $course_id,
                'feedback_id' => $value->question_id,
                'feedback' => $value->answer,
                'feedback_questions_type' => $value->question_type,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            );

            DB::table('user_feedback')->insert($data);
            
        }

        

        $course = Course::find($course_id);
        $url = "/course/$course->slug";
        $lesson = Lesson::where('course_id', $course_id)->first();
        if ($lesson) {
            $slug = $lesson->slug;
            $url = url("/lesson/$course_id/$slug");
        }

        

        $sub = SubscribeCourse::query()
                ->where('user_id', $user_id)
                ->where('course_id', $course_id)
                ->first();

        if($course->is_online == 'Offline') {
            
            if($sub->is_attended == 1) {
                $sub->update([
                    //'course_progress_status' => 2,
                    'is_completed' => 1,
                    'completed_at' => Carbon::now()
                ]);
            }
        }



        $sub->update([
            'course_progress_status' => 2,
            'has_feedback' => 1,
            'feedback_given' => 1,
            'is_completed' => 1,
            'grant_certificate' => 1,
            'completed_at' => Carbon::now()
        ]);

        if($course_id) {
            $progressdata = CustomHelper::updateUserProgress($user_id, $course_id);
        }

        return json_encode(array(
            'status' => 200,
            'message' => 'Thank you for your Feedback.',
            'url' => $url
        ));
    }






    public function store_user_details(StoreAssessmentAccountsRequest $request)
    {
        $code = random_strings(6);
        $exist_assignment_account = AssessmentAccount::where('assignment_id', $request->assignment_id)->where('email', $request->email)->first();
        if ($exist_assignment_account != null) {
            Session::flash('message', 'You have already given this assessment!');
            Session::flash('alert-class', 'alert-danger');
            return redirect()->route('online_assessment', 'assignment=' . $request->url_code)->withFlashSuccess("You have already given this assessment.");
        }
        $assessment_account = AssessmentAccount::create($request->all());
        // $assessment_account->active = isset($request->active) ? 1 : 0;
        $assessment_account->status = 1;
        $assessment_account->code = $code;
        $assessment_account->save();

        $request->session()->put('assessment_user_id', $assessment_account->id);
        $request->session()->put('assessment_assignment_id', $assessment_account->assignment_id);
        return redirect()->route('online_assessment.question_set');
    }
}
