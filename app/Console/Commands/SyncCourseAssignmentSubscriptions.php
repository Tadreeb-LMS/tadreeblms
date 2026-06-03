<?php

namespace App\Console\Commands;

use App\Helpers\CustomHelper;
use Illuminate\Console\Command;

class SyncCourseAssignmentSubscriptions extends Command
{
    protected $signature = 'courses:sync-assignment-subscriptions';

    protected $description = 'Create missing course subscriptions from course assignment records.';

    public function handle()
    {
        $synced = CustomHelper::syncCourseAssignmentAndSubscribeCourseData();

        $this->info($synced . ' course subscription(s) synced from assignments.');

        return 0;
    }
}
