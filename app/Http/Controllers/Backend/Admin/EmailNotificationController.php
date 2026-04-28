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
            'recipient_mode' => 'required|in:users,department,import,all',
            'users' => 'nullable|array',
            'users.*' => 'integer|exists:users,id',
            'department_id' => 'nullable|integer|exists:department,id',
            'import_users' => 'nullable|file|mimes:xlsx,xls|max:5120',
            'email_content' => 'required|max:5000',
            'subject' => 'required|max:500',
            'register_button' => 'required',
        ], [
            'recipient_mode.required' => __('admin_pages.email_notifications.messages.recipient_mode_required'),
            'recipient_mode.in' => __('admin_pages.email_notifications.messages.recipient_mode_required'),
            'users.*.exists' => __('admin_pages.email_notifications.messages.invalid_selected_users'),
            'department_id.exists' => __('admin_pages.email_notifications.messages.department_not_found'),
            'import_users.mimes' => __('admin_pages.email_notifications.messages.invalid_import_format'),
        ]);

        $smtpConfigMissing = empty(config('mail.mailers.smtp.host'))
            || empty(config('mail.mailers.smtp.port'))
            || empty(env('MAIL_FROM_ADDRESS'));

        if ($smtpConfigMissing) {
            return response()->json([
                'message' => __('admin_pages.email_notifications.messages.smtp_not_configured'),
            ], 400);
        }

        try {
            $recipientMode = $validated['recipient_mode'];
            $user_emails = [];

            if ($recipientMode === 'all') {
                $user_emails = User::whereHas('roles', function ($query) {
                    $query->where('name', 'student');
                })
                    ->active()
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();

            } elseif ($recipientMode === 'department') {
                $selectedDepartmentId = $request->input('department_id');

                if (empty($selectedDepartmentId)) {
                    return response()->json([
                        'errors' => [
                            'department_id' => [__('admin_pages.email_notifications.messages.no_department_selected')],
                        ],
                    ], 422);
                }

                $user_emails = DB::table('users')
                    ->join('employee_profiles', 'employee_profiles.user_id', '=', 'users.id')
                    ->where('employee_profiles.department', $selectedDepartmentId)
                    ->where('users.active', 1)
                    ->whereNull('users.deleted_at')
                    ->whereNotNull('users.email')
                    ->pluck('users.email')
                    ->toArray();

                if (empty($user_emails)) {
                    return response()->json([
                        'errors' => [
                            'department_id' => [__('admin_pages.email_notifications.messages.no_active_users_in_department')],
                        ],
                    ], 422);
                }

            } elseif ($recipientMode === 'users') {
                $selectedUserIds = $request->input('users', []);

                if (empty($selectedUserIds)) {
                    return response()->json([
                        'errors' => [
                            'users' => [__('admin_pages.email_notifications.messages.no_users_selected')],
                        ],
                    ], 422);
                }

                $user_emails = User::whereIn('id', $selectedUserIds)
                    ->active()
                    ->whereNotNull('email')
                    ->pluck('email')
                    ->toArray();

            } elseif ($recipientMode === 'import') {
                if (!$request->hasFile('import_users')) {
                    return response()->json([
                        'errors' => [
                            'import_users' => [__('admin_pages.email_notifications.messages.no_import_file')],
                        ],
                    ], 422);
                }

                $file = $request->file('import_users');
                $collection = Excel::toCollection(null, $file);

                foreach ($collection[0] as $row) {
                    foreach ($row as $cell) {
                        $email = trim($cell);

                        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                            $user_emails[] = $email;
                        }
                    }
                }

                if (empty($user_emails)) {
                    return response()->json([
                        'errors' => [
                            'import_users' => [__('admin_pages.email_notifications.messages.no_valid_emails_in_import')],
                        ],
                    ], 422);
                }
            }

            $user_emails = array_values(array_unique(array_filter($user_emails)));

            if (empty($user_emails)) {
                return response()->json([
                    'errors' => [
                        'recipient_mode' => [__('admin_pages.email_notifications.messages.select_at_least_one_recipient')],
                    ],
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

            unset($validated['import_users'], $validated['recipient_mode']);

            dispatch(new BulkEmailDispatchJob($campain_id, $user_emails, $validated));

            return response()->json([
                'message' => __('admin_pages.email_notifications.messages.notification_queued_successfully'),
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