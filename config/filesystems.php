<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Default Cloud Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Many applications store files both locally and in the cloud. For this
    | reason, you may specify a default "cloud" driver here. This driver
    | will be bound as the Cloud disk implementation in the container.
    |
    */

    'cloud' => env('FILESYSTEM_CLOUD', 's3'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3", "rackspace"
    |
    */

    'disks' => [

        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'lang' => [
            'driver' => 'local',
            'root' => base_path('resources/lang'),
        ],


        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
        'media' => [
            'driver'     => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],

        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'root' => env('AWS_ROOT', null),
            'visibility' => 'private',
        ],
        'dropbox' => [
            'driver' => 'dropbox',
            'token' => env('DROPBOX_ACCESS_TOKEN'),
            'app_secret' => env('DROPBOX_SECRET'),
        ],
        // --- External Storage Module (external-s3 disk) ---
        'external-s3' => [
            'driver' => 's3',
            'key' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ACCESS_KEY_ID'),
            'secret' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_SECRET_ACCESS_KEY'),
            'region' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_DEFAULT_REGION') ?: 'us-east-1',
            'bucket' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_BUCKET'),
            'url' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_URL') ?: null,
            'endpoint' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ENDPOINT') ?: null,
            'root' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ROOT') ?: null,
            'use_path_style_endpoint' => !empty(\App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('external-storage', 'S3_ENDPOINT')),
            'visibility' => 'private',
        ],
        // --- End External Storage Module ---

    ],

];
