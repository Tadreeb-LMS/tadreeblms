<?php

namespace App\Helpers\General;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class Active
{
    /**
     * Check if the current request uri matches a pattern.
     *
     * @param $patterns
     * @param string $class
     * @return string
     */
    public static function checkUriPattern($patterns, $class = 'active')
    {
        if (! is_array($patterns)) {
            $patterns = [$patterns];
        }

        foreach ($patterns as $pattern) {
            if (Str::is($pattern, request()->path())) {
                return $class;
            }
        }

        return '';
    }

    /**
     * Check if the current route name matches a pattern.
     *
     * @param $patterns
     * @param string $class
     * @return string
     */
    public static function checkRoute($patterns, $class = 'active')
    {
        if (! is_array($patterns)) {
            $patterns = [$patterns];
        }

        foreach ($patterns as $pattern) {
            if (Route::is($pattern)) {
                return $class;
            }
        }

        return '';
    }
}
