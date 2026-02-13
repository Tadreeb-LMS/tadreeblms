<?php

namespace App\Console\Commands;

use App\Models\Auth\User;
use App\Models\Course;
use App\Models\Stripe\SubscribeCourse;
use App\Notifications\Backend\CourseNotification;
use App\Services\NotificationSettingsService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendCourseNotifications extends Command
{
    protected $signature = 'notifications:course-reminders {--reminder-days=3 : Days before due date to send reminder}';

    protected $description = 'Send course due reminders and expired notifications';

    public function handle()
    {
        $notificationSettings = app(NotificationSettingsService::class);
        $reminderDays = (int) $this->option('reminder-days');
        $today = Carbon::today();
        $reminderDate = $today->copy()->addDays($reminderDays);

        // --- Due Reminders (3 days before) ---
        if ($notificationSettings->shouldNotify('courses', 'course_due_reminder', 'email')) {
            $this->sendDueReminders($reminderDate);
        }

        // --- Course Expired Notifications ---
        if ($notificationSettings->shouldNotify('courses', 'course_expired', 'email')) {
            $this->sendExpiredNotifications($today);
        }

        $this->info('Course notifications processed.');
    }

    protected function sendDueReminders($reminderDate)
    {
        $records = SubscribeCourse::whereDate('due_date', $reminderDate->format('Y-m-d'))
            ->where('is_completed', 0)
            ->whereNull('reminder_sent_at')
            ->whereNull('deleted_at')
            ->get();

        $count = 0;

        foreach ($records as $record) {
            $user = User::find($record->user_id);
            $course = Course::find($record->course_id);

            if (!$user || !$course) {
                continue;
            }

            try {
                CourseNotification::sendCourseDueReminderEmail($user, $course, $record->due_date);
                CourseNotification::createCourseDueReminderBell($user, $course, $record->due_date);

                DB::table('subscribe_courses')
                    ->where('id', $record->id)
                    ->update(['reminder_sent_at' => Carbon::now()]);

                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to send due reminder for user ' . $record->user_id . ', course ' . $record->course_id . ': ' . $e->getMessage());
            }
        }

        $this->info("Due reminders sent: {$count}");
        Log::info("Course due reminders sent: {$count}");
    }

    protected function sendExpiredNotifications($today)
    {
        $courses = Course::whereDate('expire_at', $today->format('Y-m-d'))
            ->get();

        $count = 0;

        foreach ($courses as $course) {
            try {
                CourseNotification::sendCourseExpiredEmail($course);
                CourseNotification::createCourseExpiredBell($course);
                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to send expired notification for course ' . $course->id . ': ' . $e->getMessage());
            }
        }

        $this->info("Expired notifications sent: {$count}");
        Log::info("Course expired notifications sent: {$count}");
    }
}
