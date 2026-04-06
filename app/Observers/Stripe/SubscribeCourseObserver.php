<?php

namespace App\Observers\Stripe;

use App\Models\Stripe\SubscribeCourse;
use App\Services\LmsEventRecorder;

class SubscribeCourseObserver
{
    /**
     * Handle SubscribeCourse "updated" events.
     *
     * @param \App\Models\Stripe\SubscribeCourse $subscribeCourse
     * @return void
     */
    public function updated(SubscribeCourse $subscribeCourse)
    {
        if (!$subscribeCourse->wasChanged('is_completed')) {
            return;
        }

        $newValue = (int) $subscribeCourse->is_completed;
        $oldValue = (int) $subscribeCourse->getOriginal('is_completed');

        if ($oldValue === 1 || $newValue !== 1) {
            return;
        }

        app(LmsEventRecorder::class)->record(
            $subscribeCourse->user_id,
            LmsEventRecorder::TYPE_COURSE_COMPLETED,
            [
                'course_id' => (int) $subscribeCourse->course_id,
                'grant_certificate' => (int) ($subscribeCourse->grant_certificate ?? 0),
                'assignment_progress' => (float) ($subscribeCourse->assignment_progress ?? 0),
                'completed_at' => optional($subscribeCourse->completed_at)->toDateTimeString(),
            ],
            $subscribeCourse->completed_at
        );
    }
}
