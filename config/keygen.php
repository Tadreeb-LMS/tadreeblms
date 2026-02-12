<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Keygen.sh Account Configuration
    |--------------------------------------------------------------------------
    |
    | Configure your Keygen.sh account details here. You can find these
    | values in your Keygen.sh dashboard under Settings > Account.
    |
    */

    'account_id' => env('KEYGEN_ACCOUNT_ID', ''),

    'product_id' => env('KEYGEN_PRODUCT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | API Configuration
    |--------------------------------------------------------------------------
    */

    'api_url' => env('KEYGEN_API_URL', 'https://api.keygen.sh/v1'),

    'api_token' => env('KEYGEN_API_TOKEN', ''),

    /*
    |--------------------------------------------------------------------------
    | Validation Settings
    |--------------------------------------------------------------------------
    |
    | cache_ttl: How long to cache license data (in minutes)
    | revalidation_interval: How often to revalidate (in hours)
    | grace_period: Days to allow usage when Keygen.sh is unreachable
    |
    */

    'cache_ttl' => env('KEYGEN_CACHE_TTL', 1440), // 24 hours

    'revalidation_interval' => env('KEYGEN_REVALIDATION_INTERVAL', 24), // hours

    'grace_period' => env('KEYGEN_GRACE_PERIOD', 7), // days
];