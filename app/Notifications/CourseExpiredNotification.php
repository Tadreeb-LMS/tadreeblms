<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class CourseExpiredNotification extends Notification
{
    use Queueable;

    protected $course;

    public function __construct($course)
    {
        $this->course = $course;
    }

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toDatabase($notifiable)
    {
        return [
            'course_id'   => $this->course->id,
            'title'       => $this->course->title,
            'message'     => 'Course "' . $this->course->title . '" has expired and was unpublished.',
            'url'         => route('admin.courses.edit', $this->course->id),
        ];
    }
}