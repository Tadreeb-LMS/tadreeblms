<?php

namespace App\Providers;

use App\Models\Auth\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

/**
 * Class RouteServiceProvider.
 */
class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Register the module autoloader so that classes under modules/{slug}/src/
     * are resolved dynamically without touching composer.json.
     */
    public function register()
    {
        parent::register();

        spl_autoload_register(function ($class) {
            // Only handle the Modules\ namespace
            if (!str_starts_with($class, 'Modules\\')) {
                return;
            }

            // Modules\Zoom\Services\ZoomMeetingService  →  ['Modules', 'Zoom', 'Services', 'ZoomMeetingService']
            $parts = explode('\\', $class);
            array_shift($parts); // Remove 'Modules'

            $slug = array_shift($parts); // 'Zoom', 'Teams', etc.

            // Convert PascalCase module name to slug (Zoom → zoom, ExternalStorage → external-storage)
            $slugDir = strtolower(preg_replace('/([a-z])([A-Z])/', '$1-$2', $slug));

            // Remaining parts form the file path inside src/
            $relativePath = implode(DIRECTORY_SEPARATOR, $parts) . '.php';
            $filePath = base_path('modules' . DIRECTORY_SEPARATOR . $slugDir . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . $relativePath);

            if (file_exists($filePath)) {
                require_once $filePath;
            }
        });
    }

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        /*
        * Register route model bindings
        */

        /*
         * Allow this to select all users regardless of status
         */
        $this->bind('user', function ($value) {
            $user = new User;

            return User::withTrashed()->where($user->getRouteKeyName(), $value)->first();
        });
        $this->configureRateLimiting();


        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapWebRoutes();

        $this->mapModuleRoutes();
    }

    /**
     * Dynamically load routes from installed external modules.
     *
     * Each module lives in {project-root}/modules/{slug}/ and may contain
     * a routes/web.php file that defines its own routes.
     */
    protected function mapModuleRoutes()
    {
        $modulesPath = base_path('modules');

        if (!is_dir($modulesPath)) {
            return;
        }

        foreach (glob($modulesPath . '/*/routes/web.php') as $routeFile) {
            // Extract the module namespace from the directory structure
            // e.g. modules/zoom/routes/web.php  →  namespace Modules\Zoom
            $slug = basename(dirname($routeFile, 2)); // "zoom", "teams", etc.
            $namespace = 'Modules\\' . str_replace(' ', '', ucwords(str_replace('-', ' ', $slug)));

            Route::middleware('web')
                ->namespace($namespace)
                ->group($routeFile);
        }
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    /**
     * Configure the rate limiters for the application.
     *
     * @return void
     */
    protected function configureRateLimiting()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60);
        });
    }
}
