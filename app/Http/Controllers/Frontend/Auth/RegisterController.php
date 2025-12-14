<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Helpers\Frontend\Auth\Socialite;
use App\Events\Frontend\Auth\UserRegistered;
use App\Mail\Frontend\Auth\AdminRegistered;
use App\Models\Auth\User;
use Arcanedev\NoCaptcha\Rules\CaptchaRule;
use Illuminate\Auth\Events\Registered;
//use Illuminate\Foundation\Auth\RegistersUsers;
use App\Repositories\Frontend\Auth\UserRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Frontend\Auth\Session;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ClosureValidationRule;
use Carbon\Carbon;
use Auth;


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/**
 * Class RegisterController.
 */
class RegisterController extends Controller
{
    //use RegistersUsers;

    /**
     * @var UserRepository
     */
    protected $userRepository;

    /**
     * RegisterController constructor.
     *
     * @param UserRepository $userRepository
     */
    public function __construct(UserRepository $userRepository)
    {

        $this->userRepository = $userRepository;
    }

    /**
     * Where to redirect users after login.
     *
     * @return string
     */
    public function redirectPath()
    {
        return route(home_route());
    }

    /**
     * Show the application registration form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showRegistrationForm()
    {
        abort_unless(config('access.registration'), 404);

        return view('frontend.auth.register')
            ->withSocialiteLinks((new Socialite)->getSocialLinks());
    }

    public function show_register()
    {

        return view('delta_academy.user.register');
    }

    /**
     * @param RegisterRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Throwable
     */
    public function register(Request $request)
    {

         if (isset($request->default_admin) && $request->default_admin == 1) {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255',
                'password' => 'required|min:6|confirmed',
                'fav_lang' => 'required',
                'g-recaptcha-response' => (config('access.captcha.registration') ? ['required', new CaptchaRule] : ''),
            ], [
                'g-recaptcha-response.required' => __('validation.attributes.frontend.captcha'),
            ]);
        } else {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|max:255',
                'last_name' => 'required|max:255',
                'email' => 'required|email|max:255|unique:users',
                'password' => 'required|min:6|confirmed',
                'fav_lang' => 'required',
                'g-recaptcha-response' => (config('access.captcha.registration') ? ['required', new CaptchaRule] : ''),
            ], [
                'g-recaptcha-response.required' => __('validation.attributes.frontend.captcha'),
            ]);
        }



        if ($validator->passes()) {
            // Store your user in database

            if (isset($request->default_admin) && $request->default_admin == 1) {
                User::where('id',1)->update(
                    [
                        'first_name' => $request->first_name,
                        'last_name' => $request->last_name,
                        'email' => $request->email,
                        'password' => Hash::make( $request->password ),
                        //'last_name' => $request->first_name,
                    ]
                );
                $redirect = 'back';
                return response(['success' => true, 'redirect' => $redirect], Response::HTTP_OK);
            } else {
                event(new Registered($user = $this->create($request->all())));

                $user->assignRole('student');
                $user->employee_type = 'external';
                $user->save();
                //Auth::loginUsingId($user->id)
                if (false) {
                    if (auth()->user()->isAdmin()) {
                        $redirect = 'dashboard';
                    } else {
                        $redirect = 'back';
                    }
                    auth()->user()->update([
                        'last_login_at' => Carbon::now()->toDateTimeString(),
                        'last_login_ip' => $request->getClientIp()
                    ]);
                    if ($request->ajax()) {
                        //dd($user->id);
                        return response(['success' => true, 'redirect' => $redirect], Response::HTTP_OK);
                    } else {
                        return response(['success' => true, 'redirect' => $redirect], Response::HTTP_OK);
                    }
                } else {
                    $redirect = 'back';
                    return response(['success' => true, 'redirect' => $redirect], Response::HTTP_OK);
                }
            }
        }

        return response(['errors' => $validator->errors()]);
    }



    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $token = sha1(time());
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'active' => 0,
            'active_token' => $token,
            'fav_lang' => $data['fav_lang']
        ]);

        $user->dob = isset($data['dob']) ? $data['dob'] : NULL;
        $user->phone = isset($data['phone']) ? $data['phone'] : NULL;
        $user->gender = isset($data['gender']) ? $data['gender'] : NULL;
        $user->address = isset($data['address']) ? $data['address'] : NULL;
        $user->city =  isset($data['city']) ? $data['city'] : NULL;
        $user->pincode = isset($data['pincode']) ? $data['pincode'] : NULL;
        $user->state = isset($data['state']) ? $data['state'] : NULL;
        $user->country = isset($data['country']) ? $data['country'] : NULL;
        $user->save();

        $userForRole = User::find($user->id);
        $userForRole->confirmed = 1;
        $userForRole->save();
        $userForRole->assignRole('student');



        // if(config('access.users.registration_mail')) {
        $this->sendAdminMail($user);
        // }

        return $user;
    }

    public function verifyUser($token)
    {

        $verifyUser = User::where('active_token', $token)->first();
        $status = '';

        if (isset($verifyUser)) {
            if ($verifyUser->active == 0) {
                $verifyUser->active = 1;
                $verifyUser->save();
                $status = "Your e-mail is verified. You can now login.";
            } else {
                $status = "Your e-mail is already verified. You can now login.";
            }
        } else {
            $status = "Invalid Token . Please try again.";
        }
        return view('frontend.auth.emailconf', compact('status'));
    }
    public function sendUserMail($user)
    {

        $to = $user->email;

        //var_dump([$to,$user]);
        // require base_path("vendor/autoload.php");
        $mail = new PHPMailer(true);     // Passing `true` enables exceptions

        try {

            // Email server settings
            $mail->SMTPDebug = 0;
            $mail->isSMTP();
            $mail->Host        = env('MAIL_HOST');
            $mail->SMTPAuth = true;
            $mail->Port     = env('MAIL_PORT');
            $mail->Username = env('MAIL_USERNAME');
            $mail->Password = env('MAIL_PASSWORD');

            // $mail->Host = env('MAIL_HOST');             //  smtp host
            // $mail->SMTPAuth = true;
            // $mail->Username = env('MAIL_USERNAME');  //  sender username
            // $mail->Password = env('MAIL_PASSWORD');       // sender password
            // $mail->SMTPSecure = 'tls';                  // encryption - ssl/tls
            // $mail->Port = 587;                          // port - 587/465

            $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
            $mail->addAddress($to);
            $mail->isHTML(true);                // Set email content format to HTML
            $mail->Subject = "New User Registered " . env('APP_NAME');
            //{{url('user/verify', $user->verifyUser->token)}};
            $mail->Body    = 'Hello ' . $user->name . '<br>

                User details are below<br>
                
                Name * ' . $user->name . ' * <br>
                Email * ' . $user->email . ' *
                <br>
                Active Link : <a href="' . url('/') . '/verify/' . $user->active_token . '">Click Here</a>
                <br>

                <br>
                Thanks for your registration,<br>' . env('APP_NAME');
            $mail->send();
            // $mail->AltBody = plain text version of email body;
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
    // private function sendAdminMail($user)
    // {
    //     $admins = User::role('administrator')->get();

    //     foreach ($admins as $admin){
    //         \Mail::to($admin->email)->send(new AdminRegistered($user));
    //     }
    // }
    public function sendAdminMail($user)
    {
        $this->sendUserMail($user);
        $admins = User::role('administrator')->get();

        foreach ($admins as $admin) {

            //require base_path("vendor/autoload.php");
            $mail = new PHPMailer(true);     // Passing `true` enables exceptions

            try {

                // Email server settings
                $mail->SMTPDebug = 0;
                $mail->isSMTP();
                $mail->Host        = env('MAIL_HOST');
                $mail->SMTPAuth = true;
                $mail->Port     = env('MAIL_PORT');
                $mail->Username = env('MAIL_USERNAME');
                $mail->Password = env('MAIL_PASSWORD');

                // $mail->Host = env('MAIL_HOST');             //  smtp host
                // $mail->SMTPAuth = true;
                // $mail->Username = env('MAIL_USERNAME');  //  sender username
                // $mail->Password = env('MAIL_PASSWORD');       // sender password
                // $mail->SMTPSecure = 'tls';                  // encryption - ssl/tls
                // $mail->Port = 587;                          // port - 587/465

                $mail->setFrom(env('MAIL_FROM_ADDRESS'), env('APP_NAME'));
                $mail->addAddress($admin->email);
                $mail->isHTML(true);                // Set email content format to HTML
                $mail->Subject = "New User Registered " . env('APP_NAME');
                $mail->Body    = "# Hello Admin<br>

                In our system new user registered, User details are below<br>
                
                Name * $user->name * <br>
                Email * $user->email *
                
                <br>
                Thanks,<br>" . env('APP_NAME');
                $mail->send();
                // $mail->AltBody = plain text version of email body;
            } catch (Exception $e) {
                echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
            }
        }
    }
}
