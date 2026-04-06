<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\BulkEmailDispatchJob;
use App\Models\Auth\User;
use App\Models\Department;
use App\Models\EmailCampain;
use App\Models\EmailCampainUser;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class EmailNotificationController extends Controller
{
    /**
     * Display a listing of Category.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendEmailNotification()
    {
        $users = User::whereHas('roles', function ($query) {
            $query->where('name', 'student');
        })->active()->latest()->select('id', 'first_name', 'last_name')->get();

        $departments = Department::select('id', 'title')->get();

        return view('backend.notification.index', compact('users', 'departments'));
    }

    public function sendEmailNotificationPost(Request $request)
    {
        $validated = $request->validate([
            'users' => 'nullable|array',
            'users.*' => 'integer|exists:users,id',
            'department_id' => 'nullable|integer|exists:department,id',
            'select_all_users' => 'nullable|boolean',
            'import_users' => 'nullable|file|mimes:xlsx,xls|max:5120',
            'email_content' => 'required|max:5000',
            'subject' => 'required|max:500',
            'register_button' => 'required',
        ], [
            'users.*.exists' => 'One or more selected users are invalid.',
            'department_id.exists' => 'Selected department does not exist.',
            'import_users.mimes' => 'Import file must be in xlsx or xls format.',
        ]);

        $smtpConfigMissing = empty(config('mail.mailers.smtp.host'))
            || empty(config('mail.mailers.smtp.port'))
            || empty(env('MAIL_FROM_ADDRESS'));

        if ($smtpConfigMissing) {
            return response()->json([
                'message' => 'SMTP is not configured. Please set MAIL_HOST, MAIL_PORT, and MAIL_FROM_ADDRESS first.',
            ], 400);
        }

        try {
            $selectedUserIds = $request->input('users', []);
            $selectedDepartmentId = $request->input('department_id');

            if ($request->boolean('select_all_users')) {
                $user_emails = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                    ->active()
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();
            } else {
                $user_emails = User::whereIn('id', $selectedUserIds)
                    ->active()
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();

                if (!empty($selectedDepartmentId)) {
                    $departmentUserEmails = DB::table('users')
                        ->join('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
                        ->where('employee_profiles.department', $selectedDepartmentId)
                        ->where('users.active', 1)
                        ->whereNull('users.deleted_at')
                        ->whereNotNull('users.email')
                        ->pluck('users.email')
                        ->toArray();

                    $user_emails = array_merge($user_emails, $departmentUserEmails);
                }
            }

            $imported_emails = [];
            if ($request->hasFile('import_users')) {
                $file = $request->file('import_users');
                $collection = Excel::toCollection(null, $file);

                foreach ($collection[0] as $row) {
                    foreach ($row as $cell) {
                        $email = trim($cell);

                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $imported_emails[] = $email;
                        }
                    }
                }
            }

            $user_emails = array_values(array_unique(array_filter(array_merge($user_emails, $imported_emails))));

            if (empty($user_emails)) {
                $hasDepartmentSelection = !empty($selectedDepartmentId);
                $hasSelectedUsers = !empty($selectedUserIds);
                $hasImportedUsers = !empty($imported_emails);
                $hasSelectAllUsers = $request->boolean('select_all_users');

                $errors = [];

                if ($hasDepartmentSelection && !$hasSelectedUsers && !$hasImportedUsers && !$hasSelectAllUsers) {
                    $errors['department_id'] = [
                        'No active users were found in the selected department. Please choose a different department or another recipient source.'
                    ];
                } else {
                    $errors['recipient'] = [
                        'Please select at least one recipient source (users, department with assigned users, import file, or send to all users).'
                    ];
                }

                return response()->json([
                    'errors' => $errors,
                ], 422);
            }

            $emailCapmain = EmailCampain::create([
                'campain_subject' => $validated['subject'],
                'content' => $validated['email_content'],
                'link' => $validated['register_button'],
            ]);

            $campain_id = $emailCapmain->id ?? null;

            $user_emails_data = [];
            foreach ($user_emails as $email) {
                $user_emails_data[] = [
                    'campain_id' => $campain_id,
                    'email' => $email,
                    'status' => 'in-queue',
                    'sent_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            if (!empty($user_emails_data)) {
                EmailCampainUser::insert($user_emails_data);
            }

            unset($validated['import_users']);

            dispatch(new BulkEmailDispatchJob($campain_id, $user_emails, $validated));

            return response()->json([
                'message' => 'Notification queued successfully',
                'redirect_route' => '/user/send-email-notification',
            ]);
        } catch (Exception $e) {
            \Log::error('EmailNotificationController: ' . $e->getMessage());

            return response()->json([
                'message' => 'Failed to send email notification. Please try again or contact support.'
            ], 400);
        }
    }
}