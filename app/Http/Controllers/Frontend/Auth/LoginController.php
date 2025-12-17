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
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use App\Repositories\Frontend\Auth\UserSessionRepository;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Cache;
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
        return route(home_route());
    }

    /**
     * Show login form with simple captcha
     */
    public function showLoginForm()
    {
        $a = rand(1, 9);
        $b = rand(1, 9);

        Session::put('captcha_answer', $a + $b);

        if (request()->ajax()) {
            return [
                'socialLinks' => (new Socialite)->getSocialLinks(),
                'captcha_question' => "$a + $b = ?"
            ];
        }

        return redirect('/')->with([
            'show_login' => true,
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
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'password' => 'required|min:6',
            'captcha' => 'required'
        ], [
            'captcha.required' => 'Please solve the captcha'
        ]);

        if ($validator->passes()) {

            // ✅ CAPTCHA CHECK
            if ((int) $request->captcha !== (int) Session::get('captcha_answer')) {
                return response([
                    'success' => false,
                    'message' => 'Invalid captcha answer'
                ], Response::HTTP_FORBIDDEN);
            }

            $credentials = $request->only($this->username(), 'password');
            $authSuccess = \Illuminate\Support\Facades\Auth::attempt(
                $credentials,
                $request->has('remember')
            );

            if ($authSuccess) {
                $request->session()->regenerate();

                if (auth()->user()->active > 0) {

                    if (isset(auth()->user()->employee_type)) {
                        if ((string) auth()->user()->employee_type == '') {
                            Session::put('setvaluesession', 1);
                        } elseif (auth()->user()->employee_type == 'internal') {
                            Session::put('setvaluesession', 2);

                            $default_language = auth()->user()->fav_lang ?? 'english';
                            if ($default_language == 'arabic') {
                                App::setLocale('ar');
                                session(['locale' => 'ar']);
                            }
                        } elseif (auth()->user()->employee_type == 'external') {
                            Session::put('setvaluesession', 3);
                        }
                    }

                    $redirect = auth()->user()->isAdmin()
                        ? '/user/dashboard'
                        : ($request->redirect_url ?? '/');

                    auth()->user()->update([
                        'last_login_at' => Carbon::now()->toDateTimeString(),
                        'last_login_ip' => $request->getClientIp()
                    ]);

                    if ($request->ajax()) {
                        return response([
                            'success' => true,
                            'redirect' => $redirect
                        ], Response::HTTP_OK);
                    }

                    return redirect('/user/dashboard');
                }

                \Illuminate\Support\Facades\Auth::logout();

                return response([
                    'success' => false,
                    'message' => 'Login failed. Account is not active'
                ], Response::HTTP_FORBIDDEN);
            }

            return response([
                'success' => false,
                'message' => 'Login failed. Account not found'
            ], Response::HTTP_FORBIDDEN);
        }

        return response([
            'success' => false,
            'errors' => $validator->errors()
        ]);
    }

    /**
     * After authentication hook
     */
    protected function authenticated(Request $request, $user)
    {
        if (! $user->isConfirmed()) {
            auth()->logout();

            if ($user->isPending()) {
                throw new GeneralException(__('exceptions.frontend.auth.confirmation.pending'));
            }

            throw new GeneralException(
                __('exceptions.frontend.auth.confirmation.resend', [
                    'url' => route(
                        'frontend.auth.account.confirm.resend',
                        $user->{$user->getUuidName()}
                    )
                ])
            );
        } elseif (! $user->isActive()) {
            auth()->logout();
            throw new GeneralException(__('exceptions.frontend.auth.deactivated'));
        }

        event(new UserLoggedIn($user));

        if (config('access.users.single_login')) {
            resolve(UserSessionRepository::class)->clearSessionExceptCurrent($user);
        }

        return redirect()->intended($this->redirectPath());
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
