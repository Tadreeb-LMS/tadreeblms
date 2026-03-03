<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Zoom API Credentials
    |--------------------------------------------------------------------------
    | These are read from the Zoom external module's own .env file, located at:
    | storage/app/external-modules/zoom/.env
    |
    | Use the External Apps admin panel to set / update these values.
    |--------------------------------------------------------------------------
    */
    'account_id'    => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('zoom', 'ZOOM_ACCOUNT_ID'),
    'client_id'     => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('zoom', 'ZOOM_CLIENT_ID'),
    'client_secret' => \App\Services\ExternalApps\ExternalAppService::staticGetModuleEnv('zoom', 'ZOOM_CLIENT_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Zoom API Settings
    |--------------------------------------------------------------------------
    */
    'base_url'          => 'https://api.zoom.us/v2/',
    'timezone'          => 'UTC',
    'auto_recording'    => 'none',
    'approval_type'     => 2, // 0-automatic, 1-manually, 2-no registration required
    'audio'             => 'both',
    'join_before_host'  => false,
    'host_video'        => false,
    'participant_video' => false,
    'mute_upon_entry'   => false,
    'waiting_room'      => false,
];
