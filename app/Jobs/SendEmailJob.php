<?php

namespace App\Jobs;

use App\Models\EmailCampainUser;
use Exception;
use Illuminate\Bus\Queueable;
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
        $details = $this->details;

        if (!filter_var(env('SMTP_ENABLED', 'true'), FILTER_VALIDATE_BOOLEAN)) {
            \Log::warning('SendEmailJob skipped: SMTP is disabled.', [
                'to_email' => $details['to_email'] ?? null,
                'subject' => $details['subject'] ?? null,
            ]);

            if (isset($details['campain_id'], $details['to_email'])) {
                EmailCampainUser::query()
                    ->where('email', $details['to_email'])
                    ->where('campain_id', $details['campain_id'])
                    ->update([
                        'status' => 'failed',
                        'sent_at' => now(),
                    ]);
            }

            return;
        }

        $to_email = $details['to_email']; //'akumar@beyondtech.club'; //$details['to_email'];
        $subject = $details['subject'];
        $html = $details['html'];

        $mailHost = config('mail.mailers.smtp.host');
        $mailPort = config('mail.mailers.smtp.port');
        $mailUsername = env('MAIL_USERNAME');
        $mailPassword = env('MAIL_PASSWORD');
        $mailFrom = env('MAIL_FROM_ADDRESS');

        if (empty($mailHost) || empty($mailPort) || empty($mailFrom)) {
            throw new Exception('SMTP configuration is incomplete. Please configure MAIL_HOST, MAIL_PORT, and MAIL_FROM_ADDRESS.');
        }

        $smtpAuthEnabled = !empty($mailUsername) && !empty($mailPassword);

        $mail = new PHPMailer(true);
        try {

            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host = $mailHost;
            $mail->SMTPAuth = $smtpAuthEnabled;
            if ($smtpAuthEnabled) {
                $mail->Username = $mailUsername;
                $mail->Password = $mailPassword;
            }

            $mailEncryption = env('MAIL_ENCRYPTION');
            if (!empty($mailEncryption)) {
                $mail->SMTPSecure = $mailEncryption;
            }
            $mail->Port = $mailPort;
            $mail->CharSet = 'UTF-8';
            $mail->Encoding = 'base64';

            $mail->setFrom($mailFrom);
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
            \Log::error('SendEmailJob failed to send email.', [
                'to_email' => $details['to_email'] ?? null,
                'subject' => $details['subject'] ?? null,
                'error' => $e->getMessage(),
            ]);

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
