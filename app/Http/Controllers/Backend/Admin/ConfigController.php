<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\Locale;
use App\Models\Config;
use App\Models\OauthClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Category;
use App\Models\Page;
use Harimayco\Menu\Models\MenuItems;

use Harimayco\Menu\Facades\Menu;
use App\Http\Requests;
use App\Models\AdminMenuItem;
use App\Models\Slider;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use LdapRecord\Container;

class ConfigController extends Controller
{
    use FileUploadTrait;

    public function getGeneralSettings()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        $lang = request()->lang ?? 'en';
        $type = config('theme_layout');
        $sections = Config::where('key', '=', 'layout_' . $type)->first();
        $footer_data = Config::where('key', '=', 'footer_data')->first();

        $logo_data = Config::where('key', '=', 'site_logo')->first();

        $footer_data = json_decode($footer_data->value);
        $sections = json_decode($sections->value);
        $app_locales = Locale::get();
        $api_clients = OauthClient::paginate(10);
        $our_vision = Config::where('key', '=', 'our_vision')->where('lang', $lang)->first();
        if (!$our_vision) {
            $our_vision = Config::create(['key' => 'our_vision', 'lang' => 'ar']);
        }
        $our_mission = Config::where('key', '=', 'our_mission')->where('lang', $lang)->first();
        if (!$our_mission) {
            $our_mission = Config::create(['key' => 'our_mission', 'lang' => 'ar']);
        }
        return view('backend.settings.general', compact('logo_data', 'sections', 'footer_data', 'app_locales', 'api_clients', 'our_vision', 'our_mission'));
    }

    public function saveGeneralSettings(Request $request)
    {
        //dd($request->all());
        //$our_vision = $request->get('our_vision');
        //$our_mission = $request->get('our_mission');
        //dd($our_mission);
        if (($request->get('mail_provider') == 'sendgrid') && ($request->get('list_selection') == 2)) {
            if ($request->get('list_name') == "") {
                return back()->withErrors(['Please input list name']);
            }
            $apiKey = config('sendgrid_api_key');
            $sg = new \SendGrid($apiKey);
            try {
                $request_body = json_decode('{"name": "' . $request->get('list_name') . '"}');
                $response = $sg->client->contactdb()->lists()->post($request_body);
                if ($response->statusCode() != 201) {
                    return back()->withErrors(['Check name and try again']);
                }
                $response = json_decode($response->body());
                $sendgrid_list_id = Config::where('sendgrid_list_id')->first();
                $sendgrid_list_id->value = $response->id;
                $sendgrid_list_id->save();
            } catch (Exception $e) {
                \Log::info($e->getMessage());
            }
        }

        $request = $this->saveFilesOptimize($request);

        //dd($request->site_logo);


        $config = Config::firstOrCreate(['key' => 'site_logo']);
        $config->value = $request->site_logo;
        $config->save();


        $switchInputs = ['access_registration', 'mailchimp_double_opt_in', 'access_users_change_email', 'access_users_confirm_email', 'access_captcha_registration', 'access_users_requires_approval', 'services__stripe__active', 'paypal__active', 'payment_offline_active', 'backup__status', 'access__captcha__registration', 'retest', 'lesson_timer', 'show_offers', 'onesignal_status', 'access__users__registration_mail', 'access__users__order_mail', 'services__instamojo__active', 'services__razorpay__active', 'services__cashfree__active', 'services__payu__active', 'flutter__active'];

        foreach ($switchInputs as $switchInput) {
            if ($request->get($switchInput) == null) {
                $requests[$switchInput] = 0;
            }
        }

        //dd($switchInputs, $requests);

        foreach ($requests as $key => $value) {
            if ($key === '_token') {
                continue;
            }

            $key = str_replace('__', '.', $key);
            $lang = request()->lang ?? 'en';
            $config = Config::firstOrCreate(['key' => $key, 'lang' => $lang]);
            $config->value = $value;
            $config->save();



            if ($key === 'app.locale') {
                Locale::where('short_name', '!=', $value)->update(['is_default' => 0]);
                $locale = Locale::where('short_name', '=', $value)->first();
                $locale->is_default = 1;
                $locale->save();
            }
        }

        //dd($requests);

        return back()->withFlashSuccess(__('alerts.backend.general.updated'));
    }

    public function saveLandingPageGeneralSettings(Request $request)
    {
        //dd($request->all());


        $value = $request->has('landing_page_toggle') ? 1 : 0;

        Config::updateOrCreate(
            ['key' => 'landing_page_toggle'],
            ['value' => $value]
        );


        return back()->withFlashSuccess(__('alerts.backend.general.updated'));
    }

    public function getLandingPageSettings(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        $lang = request()->lang ?? 'en';
        $type = config('theme_layout');
        $sections = Config::where('key', '=', 'layout_' . $type)->first();
        $footer_data = Config::where('key', '=', 'footer_data')->first();

        $logo_data = Config::where('key', '=', 'site_logo')->first();

        $landing_page_toggle = Config::where('key', 'landing_page_toggle')->value('value') ?? 0;

        $footer_data = json_decode($footer_data->value);
        $sections = json_decode($sections->value);
        $app_locales = Locale::get();
        $api_clients = OauthClient::paginate(10);
        $our_vision = Config::where('key', '=', 'our_vision')->where('lang', $lang)->first();
        if (!$our_vision) {
            $our_vision = Config::create(['key' => 'our_vision', 'lang' => 'ar']);
        }
        $our_mission = Config::where('key', '=', 'our_mission')->where('lang', $lang)->first();
        if (!$our_mission) {
            $our_mission = Config::create(['key' => 'our_mission', 'lang' => 'ar']);
        }

        $menu = Null;
        $menu_data = Null;
        if ($request->menu) {
            $menu = Menus::find($request->menu);
            $menu_data = json_decode($menu->value);
        }

        $menu_list = Menus::get();

        //dd( $menu_list );

        $pages = Page::where('published', '=', 1)->get();

        $slides_list = Slider::OrderBy('sequence', 'asc')->get();

        return view('backend.settings.landing_page_setting', compact('landing_page_toggle', 'slides_list', 'menu', 'menu_data', 'menu_list', 'pages', 'logo_data', 'sections', 'footer_data', 'app_locales', 'api_clients', 'our_vision', 'our_mission'));
    }


    public function getLdapSettings(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $lang = request()->lang ?? 'en';
        $type = config('theme_layout');

        // Read from .env instead of database
        //$ldap_toggle = env('LDAP_TOGGLE', false) ? 1 : 0;
        $ldap_toggle = Config::where('key', 'ldap_toggle')->value('value') ?? 0;
        //$ldap_connected = session('ldap_connected', 0); // since we are no longer storing in DB

        $ldap_host = env('LDAP_HOST', '127.0.0.1');
        $ldap_port = (int) env('LDAP_PORT', 1389);
        $ldap_base_dn = env('LDAP_BASE_DN', '');
        $ldap_username = env('LDAP_USERNAME', '');
        $ldap_password = env('LDAP_PASSWORD', '');
        $ldap_connected = Config::where('key', 'ldap_connected')->value('value') ?? 0;

        return view('backend.settings.ldap_setting', compact(
            'ldap_toggle',
            'ldap_connected',
            'ldap_host',
            'ldap_port',
            'ldap_base_dn',
            'ldap_username',
            'ldap_password'
        ));
    }



    public function saveLdapEnv(Request $request)
    {
        try {


            $this->setEnv([
                'LDAP_CONNECTION' => 'default',
                'LDAP_HOST' => $request->ldap_host,
                'LDAP_PORT' => (int) $request->ldap_port,
                'LDAP_BASE_DN' => $request->ldap_base_dn,
                'LDAP_USERNAME' => $request->ldap_username,
                'LDAP_PASSWORD' => $request->ldap_password,
            ]);

            Config::updateOrCreate(
                ['key' => 'ldap_toggle'],
                ['value' => $request->has('ldap_toggle') ? 1 : 0]
            );

            return response()->json([
                'status' => 'success',
                'message' => 'configuration saved successfully'
            ]);
        } catch (\Exception $e) {


            return response()->json([
                'status' => 'failed',
                'message' => "Failed saved configuration"
            ]);
        }
    }

    public function testLdapConnection(Request $request)
    {

        try {
            // Update config at runtime
            config([
                'ldap.connections.default.hosts' => [$request->ldap_host],
                'ldap.connections.default.port' => (int) $request->ldap_port,
                'ldap.connections.default.base_dn' => $request->ldap_base_dn,
                'ldap.connections.default.username' => $request->ldap_username,
                'ldap.connections.default.password' => $request->ldap_password,
            ]);



            // Now create a fresh connection with new config
            $connection = Container::getConnection();


            $connection->connect();


            return response()->json([
                'status' => 'connected',
                'message' => 'LDAP Connected Successfully'
            ]);
        } catch (\Exception $e) {


            return response()->json([
                'status' => 'failed',
                'message' => "Failed to connect to LDAP"
            ]);
        }
    }



    private function setEnv(array $data)
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            return false;
        }

        $env = File::get($envPath);

        foreach ($data as $key => $value) {
            $value = (string) $value;
            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, "{$key}={$value}", $env);
            } else {
                $env .= PHP_EOL . "{$key}={$value}";
            }
        }

        // ✅ IMPORTANT: On Windows, DON'T use File::move()
        File::put($envPath, $env);

        return true;
    }


    public function getSocialSettings()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        return view('backend.settings.social');
    }

    public function saveSocialSettings(Request $request)
    {
        $requests = request()->all();
        if ($request->get('services__facebook__active') == null) {
            $requests['services__facebook__active'] = 0;
        }
        if ($request->get('services__google__active') == null) {
            $requests['services__google__active'] = 0;
        }
        if ($request->get('services__twitter__active') == null) {
            $requests['services__twitter__active'] = 0;
        }
        if ($request->get('services__linkedin__active') == null) {
            $requests['services__linkedin__active'] = 0;
        }
        if ($request->get('services__github__active') == null) {
            $requests['services__github__active'] = 0;
        }
        if ($request->get('services__bitbucket__active') == null) {
            $requests['services__bitbucket__active'] = 0;
        }

        foreach ($requests as $key => $value) {
            if ($key != '_token') {
                $key = str_replace('__', '.', $key);
                $config = Config::firstOrCreate(['key' => $key]);
                $config->value = $value;
                $config->save();
            }
        }

        return back()->withFlashSuccess(__('alerts.backend.general.updated'));
    }

    public function getContact()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        $contact_data = config('contact_data');
        return view('backend.settings.contact', compact('contact_data'));
    }

    public function getFooter()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        $footer_data = config('footer_data');
        return view('backend.settings.footer', compact('footer_data'));
    }

    public function getNewsletterConfig()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        $newsletter_config = config('newsletter_config');
        return view('backend.settings.newsletter', compact('newsletter_config'));
    }

    public function getSendGridLists(Request $request)
    {
        $apiKey = $request->apiKey;
        $sendgrid = Config::firstOrCreate(['key' => 'sendgrid_api_key']);
        $sendgrid->value = $apiKey;
        $sendgrid->save();
        $sg = new \SendGrid($apiKey);
        try {
            $response = $sg->client->contactdb()->lists()->get();
            if ($response->statusCode() != 200) {
                return ['status' => 'error', 'message' => 'Please input valid key'];
            } else {
                return ['status' => 'success', 'body' => $response->body()];
            }
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            return ['status' => 'error', 'message' => 'Something went wrong. Please check key'];
        }
    }


    public function troubleshoot()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 1000);

        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');

        shell_exec('cd ' . base_path() . '/public');
        shell_exec('rm storage');
        \File::link(storage_path('app/public'), public_path('storage'));
        return back();
    }

    public function getZoomSettings()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }
        return view('backend.settings.zoom');
    }

    public function saveZoomSettings(Request $request)
    {
        $requests = $request->all();

        $switchInputs = ['zoom__join_before_host', 'zoom__host_video', 'zoom__participant_video', 'zoom__mute_upon_entry', 'zoom__waiting_room'];

        foreach ($switchInputs as $switchInput) {
            if ($request->get($switchInput) == null) {
                $requests[$switchInput] = 0;
            }
        }

        foreach ($requests as $key => $value) {
            if ($key != '_token') {
                $key = str_replace('__', '.', $key);
                $config = Config::firstOrCreate(['key' => $key]);
                $config->value = $value;
                $config->save();
            }
        }

        return back()->withFlashSuccess(__('alerts.backend.general.updated'));
    }
}
