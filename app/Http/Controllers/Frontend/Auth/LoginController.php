<?php

namespace App\Http\Controllers\Frontend\Auth;

use App\Helpers\Auth\Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Exceptions\GeneralException;
use App\Http\Controllers\Controller;
use App\Helpers\Frontend\Auth\Socialite;
use App\Events\Frontend\Auth\UserLoggedIn;
use App\Events\Frontend\Auth\UserLoggedOut;
use App\Helpers\CustomHelper;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Repositories\Frontend\Auth\UserSessionRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth as LaravelAuth;
use Session;
use Illuminate\Support\Facades\App;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     */
    public function redirectPath()
    {
        if (auth()->check() && auth()->user()->isAdmin()) {
            return '/admin/dashboard';
        }

        //dd("kk");

        return route(home_route());
    }

    public function refresh_captcha() {
        $captha_string = CustomHelper::getCaptcha();

       
        return response()->json([
            'captcha_question' => $captha_string,
        ]);
    }

    /**
     * Show login form with simple captcha
     */
    public function showLoginForm()
    {
       

        if (request()->ajax()) {
            $captha_string = CustomHelper::getCaptcha();
            return [
                'socialLinks' => (new Socialite)->getSocialLinks(),
                'captha' => $captha_string
            ];
        }

        $captha_string = CustomHelper::getCaptcha();
        
        return view('frontend.auth.login', [
            'captha' => $captha_string
        ]);
    }
    public function refreshCaptcha()
    {
        $a = rand(1, 9);
        $b = rand(1, 9);

        Session::put('captcha_answer', $a + $b);

        return response()->json([
            'captcha_question' => "$a + $b = ?"
        ]);
    }


    /**
     * Get login username field
     */
    public function username()
    {
        return config('access.users.username');
    }

    /**
     * Handle login request
     */
    public function login(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'email' => 'required|email|max:255',
                'password' => 'required|min:6',
                'captcha' => 'required',
            ],
            [
                'captcha.required' => 'Please solve the captcha',
            ]
        );

        if ($validator->fails()) {
            return response([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        // ✅ CAPTCHA CHECK
        if ((int) $request->captcha !== (int) Session::get('captcha_answer')) {
            return response([
                'success' => false,
                'message' => 'Invalid captcha answer',
            ], Response::HTTP_FORBIDDEN);
        }

        $credentials = $request->only($this->username(), 'password');

        if (LaravelAuth::attempt($credentials, $request->has('remember'))) {
            $user = auth()->user();

            if ($user->hasRole('administrator')) {
                $redirect = route('admin.dashboard');
                 
            } else {
                $redirect = route('admin.dashboard');
            }

           return response([
                    'success' => true,
                    'redirect' => $redirect,
            ], Response::HTTP_OK);
        }

        return response([
            'success' => false,
            'message' => 'Login failed. Account not found',
        ], Response::HTTP_FORBIDDEN);
    }

    /**
     * The user has been authenticated.
     * Role-based redirection is handled here.
     */
    protected function authenticated(Request $request, $user)
    {
        // Confirmation & active checks
        if (! $user->isConfirmed()) {
            auth()->logout();
            throw new GeneralException(__('exceptions.frontend.auth.confirmation.pending'));
        }

        if (! $user->isActive()) {
            auth()->logout();
            throw new GeneralException(__('exceptions.frontend.auth.deactivated'));
        }

        // Session values by employee type
        if (isset($user->employee_type)) {
            if (empty($user->employee_type)) {
                Session::put('setvaluesession', 1);
            } elseif ($user->employee_type === 'internal') {
                Session::put('setvaluesession', 2);

                if (($user->fav_lang ?? 'english') === 'arabic') {
                    App::setLocale('ar');
                    session(['locale' => 'ar']);
                }
            } elseif ($user->employee_type === 'external') {
                Session::put('setvaluesession', 3);
            }
        }

        // Update login stats
        $user->update([
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'last_login_ip' => $request->getClientIp(),
        ]);

        event(new UserLoggedIn($user));

        if (config('access.users.single_login')) {
            resolve(UserSessionRepository::class)
                ->clearSessionExceptCurrent($user);
        }

        // Final redirect logic
        if ($user->isAdmin()) {
            return redirect('/admin/dashboard');
        }

        $redirect = $request->redirect_url ?? $this->redirectPath();

        if ($request->ajax()) {
            return response([
                'success' => true,
                'redirect' => $redirect,
            ], Response::HTTP_OK);
        }

        return redirect()->intended($redirect);
    }

    /**
     * Logout
     */
    public function logout(Request $request)
    {
        if (app('session')->has(config('access.socialite_session_name'))) {
            app('session')->forget(config('access.socialite_session_name'));
        }

        app()->make(Auth::class)->flushTempSession();
        event(new UserLoggedOut($request->user()));

        Cache::flush();
        $this->guard()->logout();
        $request->session()->invalidate();

        return redirect()->route('frontend.index');
    }
}
