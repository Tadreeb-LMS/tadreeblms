<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkEmailDispatchJob;
use App\Jobs\SendEmailJob;
use App\Models\Auth\User;
use App\Models\Department;
use App\Models\EmailCampain;
use App\Models\EmailCampainUser;
use CustomHelper;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;

class EmailNotificationController extends Controller
{
    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmailNotification()
    {
        $users =  User::student()->latest()->select('id', 'first_name', 'last_name')->get();
        $departments = Department::select('id', 'title')->get();

        return view('backend.notification.index', compact('users', 'departments'));
    }

    public function sendEmailNotificationPost(Request $request)
    {
        $validated = $request->validate([
            //'users' => 'array|required_without_all:department_id,select_all_users,import_users',
            //'users.*' => 'integer|exists:users,id', // Validate each user ID
            //'department_id' => 'integer|required_without_all:users,select_all_users,import_users',
            //'select_all_users' => 'nullable|boolean|required_without_all:users,department_id,import_users',
            'email_content' => 'required|max:5000',
            'subject' => 'required|max:500',
            'register_button' => 'required',
            //'import_users' => 'required_without_all:users,department_id,select_all_users|file|mimes:xlsx,xls|max:5120'
        ], [
            'users.required_without' => 'Please choose either a department or atleast a user or all users',
            'department_id.required_without' => 'Please choose either a user or a department or all users',
            'select_all_users.required_without' => 'Please choose either a user or a department or all users',
        ]);

        try {
            $user_ids = @$validated['users'] ?? [];

            if (@$validated['department_id']) {
                $dep_users = DB::table('employee_profiles')
                    ->leftJoin('department', 'department.id', 'employee_profiles.department')
                    ->join('users', 'users.id', '=', 'employee_profiles.user_id')
                    ->where('users.active', 1)
                    ->whereNull('users.deleted_at')
                    ->where('department.id', '=', $validated['department_id'])
                    ->pluck('employee_profiles.user_id')->toArray();
                $user_ids = $dep_users;
            }

            if (@$validated['select_all_users']) {
                $user_emails = User::student()->latest()->pluck('email')->toArray();
            } else {
                $user_emails = User::whereIn('id', $user_ids)->pluck('email')->toArray();
            }

            $emailCapmain = EmailCampain::create(
                [
                    'campain_subject' => $validated['subject'],
                    'content' => $validated['email_content'],
                    'link' => $validated['register_button'],
                ]
            );

            $campain_id = $emailCapmain->id ?? null;

            $user_emails_data = [];
            if ($request->hasFile('import_users')) {
                $file = $request->file('import_users');
                $collection = Excel::toCollection(null, $file);

                foreach ($collection[0] as $row) {
                    foreach ($row as $cell) {
                        $email = trim($cell);

                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $user_emails[] = $email;
                            $user_emails_data[] = [
                                'campain_id' => $campain_id,
                                'email' => $email,
                                'status' => 'in-queue',
                                'sent_at' => now(),
                                'created_at' => now(),
                                'updated_at' => now()
                            ];
                        }
                    }
                }

                unset($validated['import_users']);
            }

            if (!empty($user_emails_data)) {
                EmailCampainUser::insert($user_emails_data);
            }

            $user_emails = array_unique($user_emails);

            dispatch(new BulkEmailDispatchJob($campain_id, $user_emails, $validated));

            return response()->json(['message' => 'Notification sent successfully', 'redirect_route' => '/user/send-email-notification']);
        } catch (Exception $e) {
            \Log::error('EmailNotificationController: ' . $e->getMessage());

            return response()->json(['message' => 'Failed to send email notification. Please try again or contact support.'], 400);
        }
    }
}
