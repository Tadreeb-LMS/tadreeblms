<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class SmtpSettingsController extends Controller
{
    /**
     * Display the SMTP settings form.
     */
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $smtpSettings = [
            'mail_mailer' => env('MAIL_MAILER', 'smtp'),
            'mail_host' => env('MAIL_HOST', ''),
            'mail_port' => env('MAIL_PORT', '587'),
            'mail_username' => env('MAIL_USERNAME', ''),
            'mail_password' => env('MAIL_PASSWORD', ''),
            'mail_encryption' => env('MAIL_ENCRYPTION', 'tls'),
            'mail_from_address' => env('MAIL_FROM_ADDRESS', ''),
            'mail_from_name' => env('MAIL_FROM_NAME', ''),
        ];

        return view('backend.settings.smtp', compact('smtpSettings'));
    }

    /**
     * Save SMTP settings to .env file.
     */
    public function save(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'mail_mailer' => 'required|string|in:smtp,sendmail,mailgun,ses,postmark,log',
            'mail_host' => 'required|string|max:255',
            'mail_port' => 'required|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'nullable|string|in:tls,ssl,null',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $envFile = base_path('.env');
            $envContent = file_get_contents($envFile);

            $settings = [
                'MAIL_MAILER' => $request->mail_mailer,
                'MAIL_HOST' => $request->mail_host,
                'MAIL_PORT' => $request->mail_port,
                'MAIL_USERNAME' => $request->mail_username ?? '',
                'MAIL_ENCRYPTION' => $request->mail_encryption === 'null' ? 'null' : $request->mail_encryption,
                'MAIL_FROM_ADDRESS' => $request->mail_from_address,
                'MAIL_FROM_NAME' => $request->mail_from_name,
            ];

            // Only update password if a new one is provided
            if ($request->filled('mail_password')) {
                $settings['MAIL_PASSWORD'] = $request->mail_password;
            }

            foreach ($settings as $key => $value) {
                $envContent = $this->setEnvValue($envContent, $key, $value);
            }

            file_put_contents($envFile, $envContent);

            return response()->json([
                'success' => true,
                'message' => __('labels.backend.smtp_settings.save_success')
            ]);

        } catch (\Exception $e) {
            \Log::error('SMTP Settings Save Error: ' . $e->getMessage());
            return response()->json([
                'message' => __('labels.backend.smtp_settings.save_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send a test email using current SMTP settings.
     */
    public function sendTestEmail(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'test_email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            // Dynamically configure mail settings for this request
            $mailConfig = [
                'transport' => 'smtp',
                'host' => env('MAIL_HOST'),
                'port' => env('MAIL_PORT'),
                'encryption' => env('MAIL_ENCRYPTION') === 'null' ? null : env('MAIL_ENCRYPTION'),
                'username' => env('MAIL_USERNAME'),
                'password' => env('MAIL_PASSWORD'),
                'timeout' => null,
                'auth_mode' => null,
            ];

            config(['mail.mailers.smtp' => $mailConfig]);
            config(['mail.from.address' => env('MAIL_FROM_ADDRESS')]);
            config(['mail.from.name' => env('MAIL_FROM_NAME')]);

            Mail::raw(__('labels.backend.smtp_settings.test_email_body'), function ($message) use ($request) {
                $message->to($request->test_email)
                    ->subject(__('labels.backend.smtp_settings.test_email_subject'));
            });

            return response()->json([
                'success' => true,
                'message' => __('labels.backend.smtp_settings.test_email_success')
            ]);

        } catch (\Exception $e) {
            \Log::error('SMTP Test Email Error: ' . $e->getMessage());
            return response()->json([
                'message' => __('labels.backend.smtp_settings.test_email_error') . ': ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Set or update an environment variable in the .env content.
     */
    private function setEnvValue(string $envContent, string $key, string $value): string
    {
        // Escape special characters and wrap in quotes if needed
        $escapedValue = $value;
        if (preg_match('/[\s"\'#]/', $value) || $value === '') {
            $escapedValue = '"' . addslashes($value) . '"';
        }

        // Check if key exists in env
        $keyPattern = '/^' . preg_quote($key, '/') . '=.*/m';

        if (preg_match($keyPattern, $envContent)) {
            // Update existing key
            $envContent = preg_replace($keyPattern, $key . '=' . $escapedValue, $envContent);
        } else {
            // Add new key at the end
            $envContent .= "\n" . $key . '=' . $escapedValue;
        }

        return $envContent;
    }
}
