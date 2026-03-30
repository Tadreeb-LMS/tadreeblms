<?php

namespace App\Console\Commands;

use App\Models\Course;
use App\Models\LiveSession;
use App\Models\Stripe\SubscribeCourse;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class CompleteLiveCourses extends Command
{
    protected $signature = 'courses:complete-live';
    protected $description = 'Auto-complete Live-Online courses after ALL their scheduled sessions have passed';

    public function handle()
    {
        $today = Carbon::today();

        $courses = Course::where('is_online', 'Offline')
            ->whereNotNull('last_session_date')
            ->whereIn('schedule_type', ['daily', 'weekly', 'custom'])
            ->where('last_session_date', '<', $today)
            ->where('published', 1)
            ->get();

        $completedCount = 0;

        foreach ($courses as $course) {
            // Only complete if ALL live sessions have passed
            $remainingSessions = LiveSession::where('course_id', $course->id)
                ->where('session_date', '>=', $today)
                ->count();

            if ($remainingSessions > 0) {
                Log::info("Skipping course [{$course->id}] {$course->title} — {$remainingSessions} session(s) still remaining.");
                continue;
            }

            $subscriptions = SubscribeCourse::where('course_id', $course->id)
                ->where(function ($q) {
                    $q->where('is_completed', 0)->orWhereNull('is_completed');
                })
                ->get();

            foreach ($subscriptions as $subscription) {
                $subscription->is_completed = 1;
                $subscription->completed_at = $today;
                $subscription->save();
            }

            if ($subscriptions->count() > 0) {
                $completedCount++;
                Log::info("Auto-completed live course [{$course->id}] {$course->title} — {$subscriptions->count()} student(s) marked complete.");
            }
        }

        $this->info("Completed {$completedCount} live course(s).");
    }
}
