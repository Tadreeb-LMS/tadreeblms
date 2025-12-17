<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Course;
use App\Models\FeedbackQuestion;
use App\Models\Stripe\SubscribeCourse;
use App\Models\Test;
use App\Models\TestQuestionOption;
use DB;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TestQuestionController extends Controller
{

    public function test_questions_delete($id)
    {

        DB::table('test_questions')->where('id', $id)->delete();
        return back()->withFlashSuccess(trans('alerts.backend.general.deleted'));
        print_r($id);
        die;
    }



    public function index(Request $request)
    {
        if ($request->test_id) {
            $test_questions = DB::table('test_questions')->select('test_questions.*', 'tests.title')->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')->where('test_questions.is_deleted', '=', 0)->where('test_questions.test_id', '=', $request->test_id);
        } else {
            $test_questions = DB::table('test_questions')->select('test_questions.*', 'tests.title')->leftjoin('tests', 'tests.id', '=', 'test_questions.test_id')->where('test_questions.is_deleted', '=', 0);
        }


        if ($request->course_id != "") {
            $test_questions = $test_questions->where('tests.course_id', (int)$request->course_id);
        }

        $test_questions = $test_questions->get();

        return view('backend.test_questions.index', compact('test_questions'));
    }

    public function create(Request $request, $course_id = null, $temp_id = null)
    {
        //dd($course_id, $temp_id);
        $auto_test_id = null;
        
        $course_id = $course_id ?? $request->course_id;
        $temp_id = $temp_id ?? $request->uuid; 

        if($course_id) {

        

            if( $temp_id ) {
                $course_data = Course::query()
                ->where('id',$course_id)
                //->where('temp_id',$temp_id)
                ->first();

                //dd( $course_data );

                $test_title = $course_data->title . ' - Test';

                $auto_test_data = Test::updateOrCreate(
                    [
                        'temp_id' => $temp_id,
                    ],
                    [
                        
                        'course_id' => $course_id,
                        'title' => $test_title,
                        'description' => $test_title,
                        'published' => 1
                    ]
                );
                }
                $auto_test_id = $auto_test_data->id;
            }
            
        if ($auto_test_id != NULL) {
            $tests = DB::table('tests')->where('id', $auto_test_id)->get();
        } else {
            $tests = DB::table('tests')->where('deleted_at', '=', NULL)->get();
        }
        return view('backend.test_questions.create', compact('tests', 'auto_test_id','course_id', 'temp_id' ));
    }

    public function store(Request $request)
    {

        //dd($request->all());

        if($request->question_type ==1) {
            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else if($request->question_type ==2) {

            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else { // 3 short answer
            if(empty($request->solution)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the answer',
                    'errors' => "Please provide the answer",
                ], 422);
            }
        }

        $status = $this->checkOptionValidation($options);
        if(!$status ) {
            return response()->json([
                'success' => false,
                'message' => 'At least one option must be selected.',
            ], 422);
        }

        
        if ($request->options) {
            $options = json_decode($request->options);
        }

        $question_id = DB::table('test_questions')->insertGetId([
            'temp_id' => $request->temp_id ?? null,
            'test_id' => $request->test_id,
            'question_type' => $request->question_type,
            'question_text' => $request->question,
            'solution' => $request->solution,
            'marks' => $request->marks,
            'comment' => $request->comment,
            'option_json' => $request->question_type != 3 ? $request->options : NULL,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        foreach ($options as $key => $value) {
            DB::table('test_question_options')->insert([
                'temp_id' => $request->temp_id ?? null,
                'question_id' => $question_id,
                'option_text' => $value[0],
                'is_right' => $value[1],
            ]);
        }

        //dd($request->action_btn);  /// save

        //update all users of this course whos progress is not 100
        SubscribeCourse::query()
            ->where('course_id', $request->course_id)
            ->where('grant_certificate', 0)
            ->where('has_assesment', 0)
            ->update(['has_assesment' => 1]);

        if ($request->action_btn == 'save_and_add_more') {
            //$redirect_url = route('admin.test_questions.create') . '?test_id=' . $request->test_id . '&course_id=' . $request->course_id. '&redirect=/user/assignments-nc/create';
            $redirect_url = route('admin.test_questions.create',[$request->course_id, $request->temp_id]);
        }

    

        // check if the feedback is already added then simple return back;

        if(isset($request->temp_id) && $request->action_btn == 'save') {
            $test = Test::where('temp_id',$request->temp_id)->where('id',$request->test_id)->first();
            $course_id = $test->course_id ?? null;
            if($course_id) {
                $has_feeback = FeedbackQuestion::query()
                        ->where('course_id', $course_id)
                        ->where('temp_id',$request->temp_id)
                        ->count(); 
                if($has_feeback == 0) {
                    $redirect_url = route('admin.feedback.create_course_feedback',['course_id'=>$course_id]);
                }
            }
        }

        if($request->action_btn == 'save') {
            //$redirect_url = route('admin.assessment_accounts.assignment_create') . '?assis_new&test_id=' . $request->test_id . '&course_id=' . $request->course_id;
            // By pass the asignment part
            
        
            $url_code = random_strings(20);

            Assignment::where('course_id', $request->course_id)->delete();
            
            $assignment = new Assignment();
            $assignment->temp_id = $request->temp_id ?? null;
            $assignment->url_code = $url_code;
            $assignment->course_id = $request->course_id;
            $assignment->test_id = $request->test_id;
            $assignment->save();

            

        }

        

        return json_encode(array(
            'code' => 200,
            'message' => 'Question Inserted',
            'redirect_url' => $redirect_url ?? route('admin.test_questions.index')
        ));
    }

    public function upload_ck_image(Request $request): JsonResponse
    {
        if ($request->hasFile('upload')) {
            $originName = $request->file('upload')->getClientOriginalName();
            $fileName = pathinfo($originName, PATHINFO_FILENAME);
            $extension = $request->file('upload')->getClientOriginalExtension();
            $fileName = $fileName . '_' . time() . '.' . $extension;

            $request->file('upload')->move(public_path('assets/img/ckeditor'), $fileName);

            $url = asset('assets/img/ckeditor/' . $fileName);

            return response()->json(['fileName' => $fileName, 'uploaded' => 1, 'url' => $url]);
        }
    }

    public function edit(Request $request, $id)
    {
        $test_id = $request->test_id;
        if ($test_id != NULL) {
            $tests = DB::table('tests')->where('id', $test_id)->get();
        } else {
            $tests = DB::table('tests')->where('deleted_at', '=', NULL)->get();
        }
        $question = DB::table('test_questions')->where('id', $id)->where('is_deleted', 0)->first();
        return view('backend.test_questions.edit', compact('question', 'tests'));
    }

    public function update(Request $request)
    {

        return json_encode(array(
            'code' => 200,
            'message' => 'Question Updated is not option'
        ));
        
        if($request->question_type ==1) {
            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else if($request->question_type ==2) {

            $options = isset($request->options) ? json_decode($request->options) : [];
            if(isset($request->options) && count($options) == 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the options',
                    'errors' => "Please provide the options",
                ], 422);
            }
        } else { // 3 short answer
            if(empty($request->solution)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Please provide the answer',
                    'errors' => "Please provide the answer",
                ], 422);
            }
        }

        $status = $this->checkOptionValidation($options);
        if(!$status ) {
            return response()->json([
                'success' => false,
                'message' => 'At least one option must be selected.',
            ], 422);
        }
        

        $question_id =  DB::table('test_questions')->where('id', $request->id)->where('is_deleted', 0)->first();

        if ($request->options) {
            $options = json_decode($request->options) ?? [];
        }


        DB::table('test_questions')->where('id', $request->id)->where('is_deleted', 0)->update([
            'test_id' => $request->test_id,
            'question_type' => $request->question_type,
            'question_text' => $request->question,
            'solution' => $request->solution,
            'marks' => $request->marks,
            'comment' => $request->comment,
            'option_json' => $request->question_type != 3 ? $request->options : NULL
        ]);

        TestQuestionOption::where('question_id', $question_id->id)->delete();
        
        foreach ($options as $key => $value) {

            
            TestQuestionOption::insert([
                'question_id' => $question_id->id,
                'option_text' => $value[0],
                'is_right' => $value[1],
            ]);
            

            /*
            TestQuestionOption::updateOrInsert(
                ['question_id' => $question_id->id, 'option_text' => $value[0]],
                ['question_id' => $question_id->id, 'option_text' =>$value[0], 'is_right' =>  $value[1]]
            );
            */
        }

        

        return json_encode(array(
            'code' => 200,
            'message' => 'Question Updated'
        ));
    }


    protected function checkOptionValidation($options)
    {
        //dd($options);
        $hasAtLeastOneSelected = false;
        foreach ($options as $option) {
            if (is_array($option) && isset($option[1]) && $option[1] == 1) {
                $hasAtLeastOneSelected = true;
                break;
            }
        }
        
        return $hasAtLeastOneSelected;
        
    }


    public function question_setup(Request $request)
    {
        $question_type = $request->question_type;
        // dd($question_type);
        if ($question_type == 1) {
            $view = view('backend/test_question_setup/single_choice');
            echo $view;
        } else if ($question_type == 2) {
            $view = view('backend/test_question_setup/multiple_choice');
            echo $view;
        } else {
            $view = view('backend/test_question_setup/short_answer');
            echo $view;
        }
    }

    public function question_setup_feedback(Request $request)
    {
        $question_type = $request->question_type;
        $feedbackQuestion = FeedbackQuestion::find($request->id);

        if ($question_type == 1) {
            $view = view('backend/feedback_question_setup/single_choice', compact('feedbackQuestion'));
            echo $view;
        } else if ($question_type == 2) {
            $view = view('backend/feedback_question_setup/multiple_choice', compact('feedbackQuestion'));
            echo $view;
        } else {
            $view = view('backend/feedback_question_setup/short_answer', compact('feedbackQuestion'));
            echo $view;
        }
    }
}
