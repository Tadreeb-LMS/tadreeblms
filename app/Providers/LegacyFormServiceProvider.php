<?php

declare(strict_types=1);

namespace App\Providers;

use App\Support\LegacyForm\LegacyFormBuilder;
use Illuminate\Support\ServiceProvider;

class LegacyFormServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton('legacyform', function ($app) {
            return new LegacyFormBuilder($app['url']);
        });
    }
}
