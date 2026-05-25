<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfNotInstalled
{
    public function handle(Request $request, Closure $next)
    {
        if (env('APP_INSTALLED', false) === false || env('APP_INSTALLED', false) === 'false') {
            abort(503, 'Application not installed. Run: php artisan app:install');
        }

        return $next($request);
    }
}
