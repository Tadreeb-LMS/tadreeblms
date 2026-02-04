<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use App\Models\License;

class LicenseExpiryMail extends Mailable
{
    use Queueable, SerializesModels;

     public $license;
    public $daysLeft;

    public function __construct(License $license, $daysLeft)
    {
        $this->license = $license;
        $this->daysLeft = $daysLeft;
    }

    public function build()
    {
        return $this
        ->subject('License Expiry Warning')
        ->view('emails.license_expiry');
    }
}
