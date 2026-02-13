<?php

return [
    'errors' => [
        'no_license' => 'No active license found. Please activate a valid license to continue using the system.',
        'expired' => 'Your license has expired. Please renew your license to continue using the system.',
        'revoked' => 'Your license has been revoked. Please contact support for assistance.',
        'invalid' => 'Your license is invalid. Please activate a valid license to continue.',
        'user_limit_exceeded' => 'User limit exceeded. Your license allows a maximum of :max users and you currently have :current active users.',
        'user_limit_reached' => 'Cannot create new user. Your license limit of :max users has been reached.',
    ],
    'warnings' => [
        'no_license' => 'No active license found. Please activate a license to ensure continued system access.',
        'expired' => 'Your license has expired. Please renew your license to maintain full functionality.',
        'invalid' => 'Your license is invalid or has been revoked. Please contact support.',
        'limit_exceeded' => 'User limit exceeded! You have :current active users but your license only allows :max users.',
        'limit_warning' => 'You are approaching your user limit. Currently using :current of :max users (90% threshold reached).',
    ],
    'status' => [
        'active' => 'Active',
        'expired' => 'Expired',
        'revoked' => 'Revoked',
        'invalid' => 'Invalid',
        'pending' => 'Pending',
    ],
    'messages' => [
        'activated' => 'License activated successfully.',
        'validated' => 'License validated successfully.',
        'removed' => 'License removed successfully.',
        'validation_failed' => 'License validation failed.',
    ],
];
