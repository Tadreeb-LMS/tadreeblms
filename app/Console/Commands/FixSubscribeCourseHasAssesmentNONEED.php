<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Models\LearningPathwayAssignment;
use App\Models\UserLearningPathway;
use App\Models\Stripe\SubscribeCourse;
use App\Helpers\CustomHelper;
use App\Models\Lesson;
use Illuminate\Support\Facades\DB;
use App\Models\ChapterStudent;
use App\Models\Course;

class FixSubscribeCourseHasAssesmentNONEED extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix-has-assesment';

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
            
            self::updateHasAssesment();

            self::updateHasFeedback();

            self::updateProgress();

        } catch (\Exception $e) {
            \Log::info('backup update failed - ' . $e->getMessage());
        }

        
    }

    public function updateHasAssesment()
    {
        $data = SubscribeCourse::query()
                                ->with(['course'])
                                ->orderBy('id', 'Desc')
                                //->where('course_id','308')
                                //->where('user_id','4662')
                                ->get();
            

            if($data) {
               
                foreach($data as $row) {
                   
                    
                   if($row->assignment_status == 'Passed') {
                        $row->assesment_taken = 1;
                        $row->has_assesment = 1;
                   }

                   $row->save();

                }

                //dd($users_ids);
            }
    }

    public function updateHasFeedback()
    {
        $data = SubscribeCourse::query()
                                ->with(['course'])
                                ->orderBy('id', 'Desc')
                                //->where('course_id','308')
                                //->where('user_id','4662')
                                ->get();
            

            if($data) {
               
                foreach($data as $row) {
                   
                    
                   if($row->feedback_given == 1) {
                        $row->has_feedback = 1;
                   }

                   $row->save();

                }

                //dd($users_ids);
            }
    }

    public function updateProgress()
    {
        $data = SubscribeCourse::query()
                                ->with(['course'])
                                ->orderBy('id', 'Desc')
                                //->where('course_id','308')
                                //->where('user_id','4662')
                                ->get();
            

            if($data) {
               
                foreach($data as $row) {
                    if ($row->user_id > 0 && $row->course_id > 0) {
                        CustomHelper::updateGrantCertificate($row->course_id, $row->user_id);
                    }

                }

                //dd($users_ids);
            }
    }
    
}
