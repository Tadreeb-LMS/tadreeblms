<?php

namespace App\Jobs;

use App\Models\EmailCampainUser;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use PHPMailer\PHPMailer\PHPMailer;

class SendEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $details;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($details)
    {
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (!filter_var(env('SMTP_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN)) {
            \Log::info('SendEmailJob skipped: SMTP is disabled. Subject: ' . ($this->details['subject'] ?? 'N/A'));
            return;
        }

        $details = $this->details;

        $to_email = $details['to_email']; //'akumar@beyondtech.club'; //$details['to_email'];
        $subject = $details['subject'];
        $html = $details['html'];

        $mail = new PHPMailer(true);
        try {

            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');
            $mail->SMTPSecure = 'tls';
            $mail->Port = env('MAIL_PORT');
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom(env('MAIL_FROM_ADDRESS'));
            $mail->addAddress($to_email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $html;
            $mail->send();

            //dd($mail);


            if (isset($details['campain_id'])) {
                EmailCampainUser::query()
                    ->where('email', $details['to_email'])
                    ->where('campain_id', $details['campain_id'])
                    ->update([
                        'status' => 'success',
                        'sent_at' => now()
                    ]);
            }
        } catch (Exception $e) {

            if (isset($details['campain_id'])) {
                EmailCampainUser::query()
                    ->where('email', $details['to_email'])
                    ->where('campain_id', $details['campain_id'])
                    ->update([
                        'status' => 'failed',
                        'sent_at' => now()
                    ]);
            }
        }
    }
}
