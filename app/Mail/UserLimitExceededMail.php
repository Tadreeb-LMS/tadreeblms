<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class UserLimitExceededMail extends Mailable
{
    use Queueable, SerializesModels;

    public $used;
    public $max;

    public function __construct($used,$max)
    {
        $this->used = $used;
        $this->max = $max;
    }

    public function build()
    {
        return $this
        ->subject('User Limit Exceeded')
        ->view('emails.user_limit_exceeded');
    }
}
