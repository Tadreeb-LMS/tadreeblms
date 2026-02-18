<?php

return [
    'account_id' => env('ZOOM_ACCOUNT_ID'),
    'client_id' => env('ZOOM_CLIENT_ID'),
    'client_secret' => env('ZOOM_CLIENT_SECRET'),
    'base_url' => 'https://api.zoom.us/v2/',
    'timezone' => 'UTC', // Default timezone
    'auto_recording' => 'none',
    'approval_type' => 2, // 0-automatic, 1-manually, 2-no registration required
    'audio' => 'both',
    'join_before_host' => false,
    'host_video' => false,
    'participant_video' => false,
    'mute_upon_entry' => false,
    'waiting_room' => false,
];
