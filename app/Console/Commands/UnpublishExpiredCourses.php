<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Course;
use Carbon\Carbon;
use App\Models\Auth\User;
use Spatie\Permission\Models\Role;

class UnpublishExpiredCourses extends Command
{
    protected $signature = 'courses:unpublish-expired';
    protected $description = 'Automatically unpublish expired courses';

    public function handle()
{
    // $today = \Carbon\Carbon::today();

    $today = Carbon::today()->addDay();

    $expiredCourses = \App\Models\Course::whereNotNull('expire_at')
        ->whereDate('expire_at', '<', now())
        ->where('published', 1)
        ->get();

    if ($expiredCourses->isEmpty()) {
        $this->info('No expired courses found.');
        return;
    }

    foreach ($expiredCourses as $course) {

        // Unpublish
        $course->update(['published' => 0]);

        // Notify all admins
        // $admins = \App\Models\Auth\User::where('role_id', 1)->get();
        $admins = User::role('administrator')->get();

        foreach ($admins as $admin) {
            $admin->notify(new \App\Notifications\CourseExpiredNotification($course));
        }
    }

    $this->info($expiredCourses->count() . " expired courses unpublished and admins notified.");
}
}