<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => env('SES_REGION', 'us-east-1'),
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\Auth\User::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
        'webhook' => [
            'secret' => env('STRIPE_WEBHOOK_SECRET'),
            'tolerance' => env('STRIPE_WEBHOOK_TOLERANCE', 300),
        ],
    ],

    'instamojo' => [
        'active' => false,
        'key' => env('INSTAMOJO_KEY'),
        'secret' => env('INSTAMOJO_SECRET'),
        'mode' => env('INSTAMOJO_MODE', 'sandbox')
    ],

    'razorpay' => [
        'active' => false,
        'key' => env('RAZORPAY_KEY'),
        'secret' => env('RAZORPAY_SECRET'),
    ],

    'cashfree' => [
        'active' => false,
        'app_id' => env('CASHFREE_KEY'),
        'secret' => env('CASHFREE_SECRET'),
        'mode' => env('CASHFREE_MODE', 'sandbox')
    ],

    'payu' => [
        'active' => false,
        'key' => env('PAYU_KEY'),
        'salt' => env('PAYU_SALT'),
        'mode' => env('PAYU_MODE', 'sandbox')
    ],

    /*
     * Socialite Credentials
     * Redirect URL's need to be the same as specified on each network you set up this application on
     * as well as conform to the route:
     * http://localhost/public/login/SERVICE
     * Where service can github, facebook, twitter, google, linkedin, or bitbucket
     * Docs: https://github.com/laravel/socialite
     * Make sure 'scopes' and 'with' are arrays, if their are none, use empty arrays []
     */
    'bitbucket' => [
        'active'        => env('BITBUCKET_ACTIVE'),
        'client_id'     => env('BITBUCKET_CLIENT_ID'),
        'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
        'redirect'      => env('BITBUCKET_REDIRECT'),
        'scopes'        => [],
        'with'          => [],
    ],

    'facebook' => [
        'active'        => env('FACEBOOK_ACTIVE'),
        'client_id'     => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect'      => env('FACEBOOK_REDIRECT'),
        'scopes'        => [],
        'with'          => [],
        'fields'        => [],
    ],

    'github' => [
        'active'        => env('GITHUB_ACTIVE'),
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect'      => env('GITHUB_REDIRECT'),
        'scopes'        => [],
        'with'          => [],
    ],

    'google' => [
        'active'        => env('GOOGLE_ACTIVE'),
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => env('GOOGLE_REDIRECT'),

        /*
         * Only allows google to grab email address
         * Default scopes array also has: 'https://www.googleapis.com/auth/plus.login'
         * https://medium.com/@njovin/fixing-laravel-socialite-s-google-permissions-2b0ef8c18205
         */
        'scopes' => [
            'https://www.googleapis.com/auth/plus.me',
            'https://www.googleapis.com/auth/plus.login',
        ],

        'with' => [],
    ],

    'linkedin' => [
        'active'        => env('LINKEDIN_ACTIVE'),
        'client_id'     => env('LINKEDIN_CLIENT_ID'),
        'client_secret' => env('LINKEDIN_CLIENT_SECRET'),
        'redirect'      => env('LINKEDIN_REDIRECT'),
        'scopes'        => [],
        'with'          => [],
        'fields'        => [],
    ],

    'twitter' => [
        'active'        => env('TWITTER_ACTIVE'),
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT'),
        'scopes'        => [],
        'with'          => [],
    ],

    'github_docs' => [
        'enabled' => env('GITHUB_DOCS_SYNC_ENABLED', false),
        'auto_sync_approved' => env('GITHUB_DOCS_AUTO_SYNC_APPROVED', false),
        'use_pull_requests' => env('GITHUB_DOCS_USE_PULL_REQUESTS', true),
        'token' => env('GITHUB_DOCS_TOKEN'),
        'owner' => env('GITHUB_DOCS_OWNER'),
        'repo' => env('GITHUB_DOCS_REPO'),
        'branch' => env('GITHUB_DOCS_BRANCH', 'main'),
        'path_prefix' => env('GITHUB_DOCS_PATH_PREFIX', 'docs/language-library'),
        'pull_request_title_prefix' => env('GITHUB_DOCS_PR_TITLE_PREFIX', 'chore(i18n)'),
        'committer_name' => env('GITHUB_DOCS_COMMITTER_NAME', 'TadreebLMS Bot'),
        'committer_email' => env('GITHUB_DOCS_COMMITTER_EMAIL', 'noreply@tadreeblms.com'),
    ],
];
