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
use App\Notifications\Backend\SystemNotification;
use App\Services\NotificationSettingsService;
use Illuminate\Support\Facades\App;
use App\Ldap\LdapUser;
use App\Models\Auth\User;
use App\Models\Config;
use LdapRecord\Container;
use Illuminate\Support\Str;

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

        return route(home_route());
    }

    /**
     * Show login form with simple captcha
     */
    public function showLoginForm()
    {
        if (request()->ajax()) {
            $captcha_string = CustomHelper::getCaptcha();

            return [
                'socialLinks' => (new Socialite)->getSocialLinks(),
                'captcha' => $captcha_string,
                'captcha_question' => $captcha_string,
                'captha' => $captcha_string, // backward compatibility
            ];
        }

        $captcha_string = CustomHelper::getCaptcha();

        return view('frontend.auth.login', [
            'captha' => $captcha_string,
        ]);
    }

    public function refreshCaptcha()
    {
        $captcha = CustomHelper::getCaptcha();

        return response()->json([
            'captcha' => $captcha,
            'captcha_question' => $captcha,
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

        // CAPTCHA CHECK
        if ((int) $request->captcha !== (int) Session::get('captcha_answer')) {
        return response([
            'success' => false,
            'errors' => [
                'captcha' => ['Invalid captcha answer']
            ]
        ], 422);
        }

$credentials = [
    'email' => $request->email,
    'password' => $request->password,
    'is_deleted' => 0
];

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

        // Failed login notification
        try {
            $notificationSettings = app(NotificationSettingsService::class);
            if ($notificationSettings->shouldNotify('system', 'failed_login', 'email')) {
                SystemNotification::sendFailedLoginEmail($request->email, $request->ip());
                SystemNotification::createFailedLoginBell($request->email, $request->ip());
            }
        } catch (\Exception $e) {
            \Log::error('Failed to send failed login notification: ' . $e->getMessage());
        }

        $ldap_toggle = Config::where('key', 'ldap_toggle')->value('value') ?? 0;

        if ($ldap_toggle) {
            try {
                $ldapUser = LdapUser::query()
                    ->where('mail', '=', $request->email)
                    ->first();

                if (!$ldapUser) {
                    return response([
                        'success' => false,
                        'message' => 'User not found in LDAP',
                    ], Response::HTTP_FORBIDDEN);
                }

                $dn = $ldapUser->getDn();

                $auth = Container::getInstance()
                    ->getConnection('default')
                    ->auth()
                    ->attempt($dn, $request->password);

                                if (!$auth) {
                    return response([
                        'success' => false,
                        'message' => 'Invalid LDAP password',
                    ], Response::HTTP_FORBIDDEN);
                }

                // Create or sync user in LMS database
                $user = User::updateOrCreate(
                    [
                        'email' => $request->email,
                    ],
                    [
                        'first_name' => $ldapUser->getFirstAttribute('cn'),
                        'password' => bcrypt(Str::random(16)),
                        'is_deleted' => 0,
                    ]
                );

                $user->assignRole('student');

                LaravelAuth::login($user, $request->has('remember'));

                $redirect = route('admin.dashboard');

                return response([
                    'success' => true,
                    'redirect' => $redirect,
                ], Response::HTTP_OK);
            } catch (\Exception $e) {
                return response([
                    'success' => false,
                    'message' => 'LDAP Error: ' . $e->getMessage(),
                ], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
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
        if (! $user->isConfirmed()) {
            auth()->logout();
            throw new GeneralException(__('exceptions.frontend.auth.confirmation.pending'));
        }

        if (! $user->isActive()) {
            auth()->logout();
            throw new GeneralException(__('exceptions.frontend.auth.deactivated'));
        }

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

        $user->update([
            'last_login_at' => Carbon::now()->toDateTimeString(),
            'last_login_ip' => $request->getClientIp(),
        ]);

        event(new UserLoggedIn($user));

        if (config('access.users.single_login')) {
            resolve(UserSessionRepository::class)
                ->clearSessionExceptCurrent($user);
        }

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