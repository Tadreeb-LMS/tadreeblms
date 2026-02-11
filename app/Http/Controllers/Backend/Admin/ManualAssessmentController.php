<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use App\Models\Assessment;
use App\Models\Assignment;
use App\Models\Auth\User;
use App\Models\Department;
use App\Models\ManualAssessment;
use App\Models\Test;
use Artisan;
use CustomHelper;
use DB;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Notifications\Backend\AssessmentNotification;
use App\Services\NotificationSettingsService;

class ManualAssessmentController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $data = ManualAssessment::query()->latest();

            return DataTables::of($data)
                ->addColumn('assessment_name', function ($row) {
                    return @$row->assignment->test->title;
                })
                ->addColumn('user_details', function ($row) {
                    $name = @$row->user->full_name;
                    $email = @$row->user->email;
                    return "$name</br>$email";
                })
                ->editColumn('created_at', function ($row) {
                    return @$row->created_at->format('d M Y');
                })
                ->addColumn('due_date', function ($row) {
                    return date('d M Y', strtotime($row->due_date));
                })
                ->addColumn('score', function ($q) {
                    $action_url = url("user/view-manual-assessment-answers/$q->id");
                    $answers_link = '<a target="_blank" href=' . $action_url . ' target="_blank" class="btn btn-info btn-sm"> View Answers</a>';
                    return $q->assignment_score_percentage . '%'."</br>$answers_link";
                })
                ->addColumn('status', function ($q) {
                    if ($q->assessment_id) {
                        $test_taken = CustomHelper::is_test_taken($q->assessment_id, $q->user_id);
                        if (!$test_taken) {
                            return 'Test Not Taken';
                        } else {
                            return $q->qualify_status;
                        }
                    }
                })
                ->filter(function ($query) use ($request) {
                    if ($request->has('search') && !empty($request->input('search.value'))) {
                        $search = $request->input('search.value');
                        $query->where('qualifying_percent', 'like', "%{$search}%")
                            ->orWhereHas('assignment.test', function ($query) use ($search) {
                                $query->where('title', 'like', "%{$search}%");
                            })
                            ->orWhereHas('user', function ($query) use ($search) {
                                $query->where(DB::raw("CONCAT(first_name, ' ', last_name)"), 'like', "%{$search}%");
                            })
                        ;
                    }
                })
                ->rawColumns(['user_details', 'score'])
                ->make();
        }

        return view('backend.manual-assessment.index');
    }

    public function create()
    {
        $users =  User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->active()->latest()->get()->pluck('name', 'id');

        $assignment = Assignment::join('tests', 'tests.id', 'assignments.test_id')->select('assignments.*', 'tests.title')->where('assignments.deleted_at', Null)->whereNull('assignments.course_id')->get();
        $departments = Department::all();

        return view('backend.manual-assessment.create', compact('users', 'assignment', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'assessment_id' => 'required|integer|exists:assignments,id',
            'due_date' => 'required|date|after:today',
            'qualifying_percent' => 'required|numeric|between:0,100',
            'users' => 'array|required_without:department_id',
            'users.*' => 'integer|exists:users,id', // Validate each teacher ID
            'department_id' => 'integer|required_without:users',
        ], [
            'assessment_id.required' => 'Please select a assessment',
            'assessment_id.integer' => 'Please select a valid assessment',
            'department_id.integer' => 'Please select a valid department.',
            'users.required_without' => 'Please choose either a department or atleast a user',
            'department_id.required_without' => 'Please choose either a user or a department',
        ]);

        $users = @$validated['users'];
        if (!@$validated['users']) {
            $dep_users = DB::table('employee_profiles')
                ->leftJoin('department', 'department.id', 'employee_profiles.department')
                ->join('users', 'users.id', '=', 'employee_profiles.user_id')
                ->where('users.active', 1)
                ->whereNull('users.deleted_at')
                ->where('department.id', '=', $validated['department_id'])
                ->pluck('employee_profiles.user_id')->toArray();
            $users = $dep_users;
        }

        $assignment = Assignment::find($validated['assessment_id']);
        $assessmentTitle = $assignment->course->title ?? 'Assessment';

        foreach ($users as $userId) {
            ManualAssessment::create([
                'assessment_id' => $validated['assessment_id'],
                'user_id' => $userId,
                'due_date' => $validated['due_date'],
                'qualifying_percent' => $validated['qualifying_percent'],
            ]);

            // Send assessment assigned notification
            try {
                $notificationSettings = app(NotificationSettingsService::class);
                if ($notificationSettings->shouldNotify('assessments', 'test_assigned', 'email')) {
                    $emp = User::find($userId);
                    if ($emp) {
                        AssessmentNotification::sendAssessmentAssignedEmail($emp, $assessmentTitle, $validated['due_date']);
                        AssessmentNotification::createAssessmentAssignedBell($emp, $assessmentTitle);
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Failed to send assessment assigned notification: ' . $e->getMessage());
            }
        }

        return response()->json(['message' => 'Assessment created successfully.', 'redirect_route' => route('admin.manual-assessments.index')]);
    }

    public function show($id)
    {
        $assessment = ManualAssessment::findOrFail($id);
        return response()->json($assessment);
    }

    public function edit($id)
    {
        $ma = ManualAssessment::findOrFail($id);
        $users =  User::whereHas('roles', function ($q) {
            $q->where('role_id', 3)->where('employee_type', 'external')->orWhere('employee_type', 'internal');
        })->latest()->get()->pluck('name', 'id');

        $assignment = Assignment::join('tests', 'tests.id', 'assignments.test_id')->select('assignments.*', 'tests.title')->where('assignments.deleted_at', Null)->whereNull('assignments.course_id')->get();
        $departments = Department::all();

        return view('backend.manual-assessment.edit', compact('users', 'assignment', 'departments', 'ma'));
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'assessment_id' => 'required|integer|exists:assignments,id',
            'due_date' => 'required|date|after:today',
            'qualifying_percent' => 'required|numeric|between:0,100',
        ]);

        $assessment = ManualAssessment::findOrFail($id);
        $assessment->update($validated);
        return response()->json(['message' => 'Assessment updated successfully.', 'redirect_route' => route('admin.manual-assessments.index')]);
    }

    public function destroy($id)
    {
        $assessment = ManualAssessment::findOrFail($id);
        $assessment->delete();
        return response()->json(['message' => 'Assessment deleted successfully.', 'event' => "manual_assessment_deleted"]);
    }

    public function viewUserManualAsssessementAnswers($id)
    {
        $ma = ManualAssessment::find($id);
        $user_id = $ma->user_id;
        $assignment = $ma->assignment;
        $assessement = $assignment->test;
        $marks = $ma->assignment_score;
        return view('backend.manual-assessment.user-assessment-answers', compact('assessement', 'marks', 'user_id'));
    }

    
    public function sendReminder($id)
    {
        $assessment = ManualAssessment::find($id);
        $test_link = route('manual_online_assessment', ['assignment' => $assessment->assignment->url_code, 'verify_code' => $assessment->assignment->verify_code, 'assessment_id' => $assessment->assessment_id, 'manual_assessment_id' => $assessment->id]);
        $assessment_title = $assessment->assignment->test->title;
        $due_date = date('d M Y', strtotime($assessment->due_date));

        $email_content = "# Hello " . $assessment->user->name . "<br>
        This is a friendly reminder for the $assessment_title assessment. Kindly complete it by $due_date.<br>
        <a href='" . $test_link . "'>Assessment Link</a>
        <br>
        <br>
        <br>
        Thanks,<br> " . env('APP_NAME');
        return view('backend.manual-assessment.modals.send-reminder', compact('email_content', 'id'));
    }

    public function sendReminderPost(Request $request, $id)
    {
        $assessment = ManualAssessment::find($id);
        $email_content = $request->email_content;
        $assessment_title = $assessment->assignment->test->title;
        //send a notification
        $details['to_email'] = $assessment->user->email;
        $details['subject'] = "Reminder for $assessment_title assessment | " . env("APP_NAME");
        $details['html'] = $email_content;

        dispatch(new SendEmailJob($details));

        return response()->json(['message' => 'Reminder sent successfully']);
    }

    public function sendReminderAllUsers()
    {
        return Artisan::call('reminder:manual-assignment');
    }

}
