<?php

namespace App\Console;

use App\Console\Commands\AddAssigndateDuedateInSubscribeCourses;
use App\Console\Commands\Backup;
use App\Console\Commands\CourseSlugFix;
use App\Console\Commands\CourseTypeFix;
use App\Console\Commands\DispatchSubscribeCourseJobs;
use App\Console\Commands\FixOfflineCoursesDownloadButton;
use App\Console\Commands\FixPathwaySingleRowData;
use App\Console\Commands\GenerateSitemap;
use App\Console\Commands\LearningPathwayCourseFix;
use App\Console\Commands\LessonTestChaterStudentsFix;
use App\Console\Commands\MakeNewTableForUserAssignment;
use App\Console\Commands\RemoveDuplicateInternalUsers;
use App\Console\Commands\RemoveDuplicateSubsribeCourse;
use App\Console\Commands\RemoveUnwantedFiles;
use App\Console\Commands\SendManualAssignmentReminder;
use App\Console\Commands\TeacherProfileFix;
use App\Console\Commands\UpdateAssesmentStatusAndScoreInSubscribeCourses;
use App\Console\Commands\UpdateGrantCertificateSubscribeCourses;
use App\Console\Commands\UpdateHasAssesmentSubscribeCourses;
use App\Http\Controllers\Backend\DashboardController;
use App\Console\Commands\CheckLicenseExpiry;
use App\Console\Commands\CheckUserLimit;
use App\Models\TeacherProfile;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Cache;
use Log;

/**
 * Class Kernel.
 */
class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Backup::class,
        // GenerateSitemap::class,
        // TeacherProfileFix::class,
        // LessonTestChaterStudentsFix::class,
        // RemoveUnwantedFiles::class,
        // UpdateAssesmentStatusAndScoreInSubscribeCourses::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {

        
        $schedule->command(DispatchSubscribeCourseJobs::class)->daily()->withoutOverlapping();
        $schedule->command(SendManualAssignmentReminder::class)->daily()->withoutOverlapping();
        $schedule->command('license:expiry-check')->daily();
        $schedule->command('license:user-limit-check')->daily();
        //$schedule->command(FixOfflineCoursesDownloadButton::class)->daily()->withoutOverlapping();
        
        // Once for data fix
        

        /*
        $schedule->command(CourseSlugFix::class)->daily()->withoutOverlapping();
        $schedule->command(CourseTypeFix::class)->daily()->withoutOverlapping();

        //empty the table first
        $schedule->command(MakeNewTableForUserAssignment::class)->daily()->withoutOverlapping();
        $schedule->command(FixPathwaySingleRowData::class)->daily()->withoutOverlapping();

        $schedule->command(RemoveDuplicateSubsribeCourse::class)->daily()->withoutOverlapping();
        $schedule->command(RemoveDuplicateInternalUsers::class)->daily()->withoutOverlapping();

        $schedule->command(AddAssigndateDuedateInSubscribeCourses::class)->hourly()->withoutOverlapping();
        

        //last 6 months - not
        $schedule->command(UpdateAssesmentStatusAndScoreInSubscribeCourses::class)->hourly()->withoutOverlapping();
        
        $schedule->command(UpdateHasAssesmentSubscribeCourses::class)->daily()->withoutOverlapping();
        $schedule->command(FixOfflineCoursesDownloadButton::class)->daily()->withoutOverlapping();
        $schedule->command(UpdateGrantCertificateSubscribeCourses::class)->hourly()->withoutOverlapping();
        

        
        

        

        //$schedule->command(LearningPathwayCourseFix::class)->daily()->withoutOverlapping();
        
        */


         $schedule->call(function () {
            Log::info('Warming up dashboard cache...');
            try {
                $controller = new DashboardController();
                $controller->buildDashboardCache(30);
                Log::info('✅ Dashboard cache warmed successfully.');
            } catch (\Throwable $e) {
                Log::error('❌ Dashboard cache warm failed: ' . $e->getMessage());
            }

            Log::info('Dashboard cache warmed successfully.');
         })->everyThirtyMinutes();
        
        
        
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
