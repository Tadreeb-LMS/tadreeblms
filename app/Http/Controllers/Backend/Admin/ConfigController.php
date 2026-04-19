<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Traits\FileUploadTrait;
use App\Models\Locale;
use App\Models\Config;
use App\Models\LanguageMarketplacePackage;
use App\Models\LanguageTranslationInvitation;
use App\Models\OauthClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

use App\Models\Category;
use App\Models\Page;
use Bdhabib\LaravelMenu\Models\MenuItems;

use Bdhabib\LaravelMenu\Facades\LaravelMenu;
use App\Http\Requests;
use App\Models\AdminMenuItem;
use App\Models\Slider;
use Bdhabib\LaravelMenu\Models\Menus;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;
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
        $sourcePackage = null;
        $publishedLanguagePackages = collect();
        $translationInvitations = collect();
        $pendingLanguageSubmissions = collect();

        if (Schema::hasTable('language_marketplace_packages')) {
            $sourcePackage = LanguageMarketplacePackage::query()
                ->where('package_type', 'source')
                ->where('status', 'published')
                ->latest('published_at')
                ->first();

            $publishedLanguagePackages = LanguageMarketplacePackage::query()
                ->where('status', 'published')
                ->latest('published_at')
                ->limit(20)
                ->get();

            $pendingLanguageSubmissions = LanguageMarketplacePackage::query()
                ->where('status', 'submitted')
                ->latest('submitted_at')
                ->limit(20)
                ->get();
        }

        if (Schema::hasTable('language_translation_invitations')) {
            $translationInvitations = LanguageTranslationInvitation::query()
                ->latest()
                ->limit(20)
                ->get();
        }

        $our_vision = Config::where('key', '=', 'our_vision')->where('lang', $lang)->first();
        if (!$our_vision) {
            $our_vision = Config::create(['key' => 'our_vision', 'lang' => 'ar']);
        }
        $our_mission = Config::where('key', '=', 'our_mission')->where('lang', $lang)->first();
        if (!$our_mission) {
            $our_mission = Config::create(['key' => 'our_mission', 'lang' => 'ar']);
        }
        return view('backend.settings.general', compact(
            'logo_data',
            'sections',
            'footer_data',
            'app_locales',
            'api_clients',
            'our_vision',
            'our_mission',
            'sourcePackage',
            'publishedLanguagePackages',
            'translationInvitations',
            'pendingLanguageSubmissions'
        ));
    }

    public function saveGeneralSettings(Request $request)
    {
        if ($request->filled('language_action')) {
            return $this->handleLanguageLibraryAction($request);
        }

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

    public function downloadLanguageLibrary($locale)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $localeCode = strtolower(trim((string) $locale));
        $localeModel = Locale::where('short_name', $localeCode)->first();
        if (!$localeModel) {
            return back()->withErrors(['Locale not found.']);
        }

        $langPath = resource_path('lang/' . $localeCode);
        if (!File::isDirectory($langPath)) {
            return back()->withErrors(['No language files found for selected locale.']);
        }

        $modules = [];
        foreach (File::files($langPath) as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $module = pathinfo($file->getFilename(), PATHINFO_FILENAME);
            $content = include $file->getRealPath();
            if (is_array($content)) {
                $modules[$module] = $content;
            }
        }

        $payload = [
            'locale' => $localeCode,
            'generated_at' => now()->toIso8601String(),
            'modules' => $modules,
        ];

        $fileName = 'language-library-' . $localeCode . '.json';
        return response()->streamDownload(function () use ($payload) {
            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, ['Content-Type' => 'application/json']);
    }

    protected function handleLanguageLibraryAction(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $action = (string) $request->input('language_action');
        if ($action === 'upload') {
            return $this->handleLanguageLibraryUpload($request);
        }

        if (Str::startsWith($action, 'toggle:')) {
            return $this->handleLanguageToggle($action);
        }

        return back()->withErrors(['Unsupported language action requested.']);
    }

    protected function handleLanguageLibraryUpload(Request $request)
    {
        $request->validate([
            'language_target_locale' => 'required|string|max:15',
            'language_payload_file' => 'nullable|file|mimes:json,txt|max:5120',
            'language_payload_json' => 'nullable|string',
        ]);

        $localeCode = strtolower(trim((string) $request->input('language_target_locale')));
        $localeModel = Locale::where('short_name', $localeCode)->first();
        if (!$localeModel) {
            return back()->withErrors(['Selected locale does not exist.']);
        }

        $rawPayload = trim((string) $request->input('language_payload_json', ''));
        if ($request->hasFile('language_payload_file')) {
            $rawPayload = (string) file_get_contents($request->file('language_payload_file')->getRealPath());
        }

        if ($rawPayload === '') {
            return back()->withErrors(['Upload a JSON file or provide JSON payload text.']);
        }

        $decoded = json_decode($rawPayload, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            return back()->withErrors(['Invalid JSON payload.']);
        }

        $modules = $this->extractLanguageModules($decoded);
        if (empty($modules)) {
            return back()->withErrors(['No translation modules found in uploaded payload.']);
        }

        $langDir = resource_path('lang/' . $localeCode);
        if (!File::isDirectory($langDir)) {
            File::makeDirectory($langDir, 0755, true);
        }

        foreach ($modules as $module => $translations) {
            if (!is_array($translations)) {
                continue;
            }

            $moduleName = strtolower(trim((string) $module));
            $moduleName = preg_replace('/[^a-z0-9_]+/', '_', $moduleName);
            if ($moduleName === '') {
                continue;
            }

            $modulePath = $langDir . DIRECTORY_SEPARATOR . $moduleName . '.php';
            $existing = [];
            if (File::exists($modulePath)) {
                $loaded = include $modulePath;
                if (is_array($loaded)) {
                    $existing = $loaded;
                }
            }

            $normalizedTranslations = $this->normalizeTranslationsArray($translations);
            $merged = array_replace_recursive($existing, $normalizedTranslations);

            $php = "<?php\n\nreturn " . var_export($merged, true) . ";\n";
            File::put($modulePath, $php);
        }

        $packageDir = storage_path('app/language-library/' . $localeCode);
        if (!File::isDirectory($packageDir)) {
            File::makeDirectory($packageDir, 0755, true);
        }

        $packageName = now()->format('Ymd_His') . '.json';
        $relativePackagePath = 'language-library/' . $localeCode . '/' . $packageName;
        File::put(storage_path('app/' . $relativePackagePath), json_encode($decoded, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        if (Schema::hasColumn('locales', 'library_package_path')) {
            $localeModel->library_package_path = $relativePackagePath;
        }
        if (Schema::hasColumn('locales', 'library_uploaded_at')) {
            $localeModel->library_uploaded_at = now();
        }
        $localeModel->save();

        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess('Language library uploaded successfully.');
    }

    protected function handleLanguageToggle($toggleAction)
    {
        $parts = explode(':', (string) $toggleAction);
        if (count($parts) !== 3) {
            return back()->withErrors(['Invalid toggle payload.']);
        }

        $localeCode = strtolower(trim($parts[1]));
        $targetEnabled = (int) $parts[2] === 1 ? 1 : 0;

        $localeModel = Locale::where('short_name', $localeCode)->first();
        if (!$localeModel) {
            return back()->withErrors(['Selected locale does not exist.']);
        }

        if ($targetEnabled === 0 && (int) $localeModel->is_default === 1) {
            return back()->withErrors(['Default language cannot be disabled.']);
        }

        if (Schema::hasColumn('locales', 'is_enabled')) {
            if ($targetEnabled === 0) {
                $enabledCount = Locale::where('is_enabled', 1)->count();
                if ($enabledCount <= 1) {
                    return back()->withErrors(['At least one language must remain enabled.']);
                }
            }
            $localeModel->is_enabled = $targetEnabled;
            $localeModel->save();
        }

        $message = $targetEnabled ? 'Language enabled successfully.' : 'Language disabled successfully.';
        return redirect()->route('admin.general-settings', ['tab' => 'language_settings'])
            ->withFlashSuccess($message);
    }

    protected function extractLanguageModules(array $decoded)
    {
        if (isset($decoded['module'], $decoded['translations']) && is_array($decoded['translations'])) {
            return [$decoded['module'] => $decoded['translations']];
        }

        if (isset($decoded['modules']) && is_array($decoded['modules'])) {
            return $decoded['modules'];
        }

        if (isset($decoded['locale']) && count($decoded) === 2 && is_array($decoded['translations'] ?? null)) {
            return ['messages' => $decoded['translations']];
        }

        $reserved = ['locale', 'generated_at', 'meta'];
        $modules = Arr::except($decoded, $reserved);
        return is_array($modules) ? $modules : [];
    }

    protected function normalizeTranslationsArray(array $translations)
    {
        $flat = Arr::dot($translations);
        return Arr::undot($flat);
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

        // Read toggle state from database (stored by saveLdapEnv)
        $ldap_toggle = (int) (Config::where('key', 'ldap_toggle')->value('value') ?? 0);

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

            // Save toggle state: explicitly check the value sent from JavaScript
            // If not present or falsy, save as 0; if present and 1, save as 1
            $toggle_value = (int) $request->input('ldap_toggle', 0);
            Config::updateOrCreate(
                ['key' => 'ldap_toggle'],
                ['value' => $toggle_value]
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

            // Escape backslashes and double quotes so the value can be safely quoted
            $escaped = str_replace(["\\", '"'], ["\\\\", '\\"'], $value);

            // If the value contains whitespace, save it as a quoted string in .env
            if (preg_match('/\s/', $escaped)) {
                $valueForEnv = '"' . $escaped . '"';
            } else {
                $valueForEnv = $escaped;
            }

            $pattern = "/^{$key}=.*$/m";

            if (preg_match($pattern, $env)) {
                $env = preg_replace($pattern, "{$key}={$valueForEnv}", $env);
            } else {
                $env .= PHP_EOL . "{$key}={$valueForEnv}";
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

    public function getCertificateTemplateSettings()
    {
        $settings = Config::where('key', 'certificate_template_settings')->first();
        if ($settings) {
            $settings = json_decode($settings->value, true);
        } else {
            $settings = [
                'template' => 'classic-dark',
                'primary_color' => '#d4af37',
                'secondary_color' => '#f5d670',
                'bg_color' => '#1a1a2e',
                'text_color' => '#ffffff',
                'cert_label' => 'Certificate of Completion',
                'cert_title' => 'Achievement Award',
                'show_badge' => 1,
                'show_seal' => 1,
                'show_signature' => 1,
                'logo_image' => null,
                'seal_image' => null,
                'signature_image' => null,
            ];
        }
        return view('backend.settings.certificate-template', compact('settings'));
    }

    public function saveCertificateTemplateSettings(Request $request)
    {
        $oldSettings = Config::where('key', 'certificate_template_settings')->first();
        $oldSettings = $oldSettings ? json_decode($oldSettings->value, true) : [];

        $request = $this->saveFilesOptimize($request);
        
        $settings = $request->only([
            'template', 'bg_texture', 'primary_color', 'secondary_color', 'bg_color', 'text_color', 
            'cert_label', 'cert_title', 'show_badge', 'show_seal', 'show_signature'
        ]);

        // Handle image uploads
        $settings['logo_image'] = is_string($request->get('logo_image')) ? $request->get('logo_image') : ($oldSettings['logo_image'] ?? null);
        $settings['seal_image'] = is_string($request->get('seal_image')) ? $request->get('seal_image') : ($oldSettings['seal_image'] ?? null);
        $settings['signature_image'] = is_string($request->get('signature_image')) ? $request->get('signature_image') : ($oldSettings['signature_image'] ?? null);

        if ($request->has('remove_logo_image')) {
            $settings['logo_image'] = null;
        }
        if ($request->has('remove_seal_image')) {
            $settings['seal_image'] = null;
        }
        if ($request->has('remove_signature_image')) {
            $settings['signature_image'] = null;
        }

        // Ensure toggles are 0 if not present in request
        $settings['show_badge'] = $request->has('show_badge') ? 1 : 0;
        $settings['show_seal'] = $request->has('show_seal') ? 1 : 0;
        $settings['show_signature'] = $request->has('show_signature') ? 1 : 0;
        
        Config::updateOrCreate(
            ['key' => 'certificate_template_settings'],
            ['value' => json_encode($settings)]
        );

        return back()->withFlashSuccess(__('alerts.backend.general.updated'));
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

    public function downloadBaseLanguageFile()
    {
        if (!auth()->user()->isAdmin()) {
            return abort(403);
        }

        $langPath = resource_path('lang/en');
        $merged = [];

        foreach (File::files($langPath) as $file) {
            if ($file->getExtension() === 'php') {
                $keys = require $file->getPathname();
                if (is_array($keys)) {
                    $merged[$file->getFilenameWithoutExtension()] = $keys;
                }
            }
        }

        $json = json_encode($merged, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        return response($json, 200, [
            'Content-Type'        => 'application/json',
            'Content-Disposition' => 'attachment; filename="en.json"',
        ]);
    }
}
