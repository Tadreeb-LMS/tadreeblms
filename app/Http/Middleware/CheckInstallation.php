<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckInstallation
{
    public function handle(Request $request, Closure $next)
    {
        if (!file_exists(base_path('.env')) || in_array(env('APP_INSTALLED'), [false, 'false'], true)) {
            abort(503, 'Application not installed. Run: php artisan app:install');
        }

        return $next($request);
    }
}
