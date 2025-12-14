<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Mail\ResetPassword;
use App\Http\Controllers\Controller;
use App\Jobs\SendEmailJob;
use Carbon\Carbon;
//use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Validator;
use App\Models\Auth\User;
use App\Models\PasswordReset;
use CustomHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
/**
 * Class ForgotPasswordController.
 */
class ForgotPasswordController extends Controller
{
    //use SendsPasswordResetEmails;

    /**
     * Display the form to request a password reset link.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLinkRequestForm()
    {
        return view('frontend.auth.passwords.email');
    }

    public function changePassword($token)
    {
        PasswordReset::where('token', $token)->firstOrFail();
        return view('frontend.auth.passwords.change-password');
    }

    public function changePasswordPost(Request $request)
    {
        $validated = $request->validate([
            'password' => [
                'required', 
                'string', 
                'min:8', // Minimum 8 characters
                'confirmed', // Ensures password_confirmation matches
            ],
            'token' => 'required|max:32'
        ]);

        $token = $validated['token'];
        $pr = PasswordReset::where('token', $token)->firstOrFail();

        User::where('email', $pr->email)->update([
            'password' => Hash::make($validated['password'])
        ]);

        PasswordReset::where('token', $token)->delete();
        return response()->json(['message'=> 'Password changed successfully', 'redirect_route'=> '/?openModal']);

        return view('frontend.auth.passwords.change-password');
    }

    public function sendResetLinkEmail_bkup(Request $request)
    {
        // dd('hi');
        // $validator = Validator::make($request->all(), [
        //     'email' => ['required', 'email',
        //                  Rule::exists('users')->where(function ($query) {
        //                      $query->where('active', 1);
        //                  })
        //                ]
        //      ]);
       // var_dump($request->only('email'));
        $this->validateEmail($request);

       // $message->getFrom();

        // We will send the password reset link to this user. Once we have attempted
        // to send the link, we will examine the response then see the message we
        // need to show to the user. Finally, we'll send out a proper response.
        $response = $this->broker()->sendResetLink(
            $request->only('email')
        );

        return $response == Password::RESET_LINK_SENT
            ? $this->sendResetLinkResponse($request, $response)
            : $this->sendResetLinkFailedResponse($request, $response);
    }
    public function sendUserMail($user)
    {
        $to = $user->email;
        $mail = new PHPMailer(true);     // Passing `true` enables exceptions
    
        try {

            $token = Str::random(32);
            PasswordReset::updateOrCreate([
                'email' => $to
            ],[
                'token' => $token
            ]);
            $password_reset_link = url("change-password/$token");

            $user_fav_lang = $user->fav_lang;
            $username = $user->full_name;

            if ($user_fav_lang == 'arabic') {
                $username = $user->arabic_full_name??$user->full_name;
            }

            $variables = [
                '{User_Name}' => $username,
                '{Link}' => $password_reset_link,
            ];
            
            $email_template = CustomHelper::emailTemplates('reset_password', $user_fav_lang, $variables);

            $details['to_email'] = $user->email;
            $details['subject'] = $email_template['subject'];
            $details['html'] = $email_template['email_content'];

            //dd($details);

            dispatch(new SendEmailJob($details));

            // $mail->AltBody = plain text version of email body;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
        
    }
    public function sendResetLinkEmail(Request $request)
        {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email'],
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }
        
        $verify = User::where('email', $request->all()['email'])->firstOrFail();

        $this->sendUserMail($verify);
        return back()->with('status', 'Reset password link has been sent successfully.');
    }
}
