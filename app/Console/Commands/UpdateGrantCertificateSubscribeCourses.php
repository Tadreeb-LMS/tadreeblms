<?php

namespace App\Console\Commands;

use App\Helpers\CustomHelper;
use App\Jobs\SendEmailJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LearningPathwayAssignment;
use App\Models\LearningPathwayCourse;
use App\Models\courseAssignment;
use App\Models\Stripe\SubscribeCourse;
use Illuminate\Support\Facades\DB;

class UpdateGrantCertificateSubscribeCourses extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-grant-certificate-subscribecourses';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        
         
        try {
            
            SubscribeCourse::with('user', 'course')
            ->where('is_completed', 1)
            ->where('grant_certificate', '0')
            ->whereHas('course')
            ->orderBy('id', 'Desc')
            ->chunk(100, function ($chunk) {
                foreach ($chunk as $row) {
                    if ($row->user_id > 0 && $row->course_id > 0) {
                        // Keep certificate/completion decisions in one place.
                        CustomHelper::updateGrantCertificate($row->course_id, $row->user_id);
                    }
                }
            });

        } catch (\Exception $e) {
            \Log::info('backup update failed - ' . $e->getMessage());
        }

        
    }
}
