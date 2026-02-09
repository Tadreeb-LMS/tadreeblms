<?php

namespace App\Providers;

use App\Helpers\Frontend\Auth\Socialite;
use App\Models\Auth\User;
use App\Models\Locale;
use App\Models\Blog;
use App\Models\Config;
use App\Models\Course;
use App\Models\Slider;
use Barryvdh\TranslationManager\Manager;
use Barryvdh\TranslationManager\Models\Translation;
use Carbon\Carbon;
use Harimayco\Menu\Facades\Menu;
use Harimayco\Menu\Models\MenuItems;
use Harimayco\Menu\Models\Menus;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Resolvers\SocialUserResolver;
use Coderello\SocialGrant\Resolvers\SocialUserResolverInterface;
use App\Helpers\CustomHelper;

/**
 * Class AppServiceProvider.
 */
class AppServiceProvider extends ServiceProvider
{

    public $bindings = [
        SocialUserResolverInterface::class => SocialUserResolver::class,
    ];


    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (app()->runningInConsole() 
            || !Schema::hasTable('locales')) {
            return;
        }
        /*
         * Application locale defaults for various components
         *
         * These will be overridden by LocaleMiddleware if the session local is set
         */

        /*
         * setLocale for php. Enables ->formatLocalized() with localized values for dates
         */
        setlocale(LC_TIME, config('app.locale_php'));


        /*
         * Set the session variable for whether or not the app is using RTL support
         * For use in the blade directive in BladeServiceProvider
         */
        //        if (! app()->runningInConsole()) {
        //            if (config('locale.languages')[config('app.locale')][2]) {
        //                session(['lang-rtl' => true]);
        //            } else {
        //                session()->forget('lang-rtl');
        //            }
        //        }

        // Force SSL in production
        if ($this->app->environment() == 'production') {
            //URL::forceScheme('https');
        }

        // Set the default string length for Laravel5.4
        Schema::defaultStringLength(191);

        // Set the default template for Pagination to use the included Bootstrap 4 template
        \Illuminate\Pagination\AbstractPaginator::defaultView('pagination::bootstrap-4');
        \Illuminate\Pagination\AbstractPaginator::defaultSimpleView('pagination::simple-bootstrap-4');


        if (Schema::hasTable('configs')) {
            foreach (Config::all() as $setting) {
                \Illuminate\Support\Facades\Config::set($setting->key, $setting->value);
            }
            \Illuminate\Support\Facades\Config::set('cashier.key', config('services.stripe.key'));
            \Illuminate\Support\Facades\Config::set('cashier.secret', config('services.stripe.secret'));
        }

        /*
         * setLocale to use Carbon source locales. Enables diffForHumans() localized
         */

        Carbon::setLocale(config('app.locale'));
        App::setLocale(config('app.locale'));
        config()->set('invoices.currency', config('app.currency'));

        if (Schema::hasTable('configs')) {
            $logo_data = Config::where('key', '=', 'site_logo')->first();
            //dd($logo_data);
            View::share('site_logo', $logo_data);
        } else {
            
            View::share('site_logo', null);
        }

        if (Schema::hasTable('sliders')) {
            $slides = Slider::where('status', 1)->orderBy('sequence', 'asc')->get();
            View::share('slides', $slides);
        }

        $disabled_landing_page = CustomHelper::redirect_based_on_setting();
        View::share('disabled_landing_page', $disabled_landing_page);

        if (Schema::hasTable('admin_menu_items') && $disabled_landing_page == 0) {

            $menu_name = NULL;
            $custom_menus = MenuItems::where('menu', '=', config('nav_menu'))
                ->orderBy('sort')
                ->get();
            $menu_name = Menus::find((int)config('nav_menu'));
            $menu_name = ($menu_name != NULL) ? $menu_name->name : NULL;
            $custom_menus = menuList($custom_menus);
            $max_depth = MenuItems::max('depth');
            View::share('custom_menus', $custom_menus);
            View::share('max_depth', $max_depth);
            View::share('menu_name', $menu_name);
        } else {
            View::share('custom_menus', []);
            View::share('max_depth', 0);
            View::share('menu_name', null);
        }

        //        view()->composer(['frontend.layouts.partials.right-sidebar', 'frontend-rtl.layouts.partials.right-sidebar'], function ($view) {

        if (Schema::hasTable('blogs')) {

            $recent_news = Blog::orderBy('created_at', 'desc')->whereHas('category')->take(2)->get();
            View::share('recent_news', $recent_news);
        }
        //
        //            $view->with(compact('recent_news'));
        //        });


        //        view()->composer(['frontend.*', 'frontend-rtl.*'], function ($view) {

        if (Schema::hasTable('courses')) {

            $global_featured_course = Course::withoutGlobalScope('filter')->canDisableCourse()
                ->whereHas('category')
                ->where('published', '=', 1)
                ->where('featured', '=', 1)->where('trending', '=', 1)->first();

            $featured_courses = Course::withoutGlobalScope('filter')->canDisableCourse()->where('published', '=', 1)
                ->whereHas('category')
                ->where('featured', '=', 1)->take(8)->get();

            //            $view->with(compact('global_featured_course','featured_courses'));
            //        });
            View::share('global_featured_course', $global_featured_course);
            View::share('featured_courses', $featured_courses);
        }
        //        view()->composer(['frontend.*', 'backend.*', 'frontend-rtl.*', 'vendor.invoices.*'], function ($view) {
        if (Schema::hasTable('locales')) {

            $locales = [];
            $appCurrency = getCurrency(config('app.currency'));

            if (Schema::hasTable('locales')) {
                $locales = Locale::whereIn('short_name', ['en', 'ar'])->pluck('short_name as locale')->toArray();
            }
            //            $view->with(compact('locales', 'appCurrency'));

            //        });
            View::share('locales', $locales);
            View::share('appCurrency', $appCurrency);




            //        view()->composer(['backend.*'], function ($view) {

            $locale_full_name = 'English';
            $locale = Locale::where('short_name', '=', config('app.locale'))->first();
            if ($locale) {
                $locale_full_name = $locale->name;
            }

            View::share('locale_full_name', $locale_full_name);

            $default_user = User::where('id',1)->first();

            //dd($default_user);

            View::share('default_admin_email', $default_user);

            //            $view->with(compact('locale_full_name'));
            //        });
        }
    }

    function menuList($array)
    {
        $temp_array = array();
        foreach ($array as $item) {
            if ($item->getsons($item->id)->except($item->id)) {
                $item->subs = $this->menuList($item->getsons($item->id)->except($item->id)); // here is the recursion
                $temp_array[] = $item;
            }
        }
        return $temp_array;
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        /*
         * Sets third party service providers that are only needed on local/testing environments
         */
        if ($this->app->environment() != 'production') {
            /**
             * Loader for registering facades.
             */
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();

            /*
             * Load third party local aliases
             */
            $loader->alias('Debugbar', \Barryvdh\Debugbar\Facade::class);
        }
        \Illuminate\Support\Collection::macro('lists', function ($a, $b = null) {
            return collect($this->items)->pluck($a, $b);
        });
    }
}
