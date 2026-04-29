<?php

return [
    'page_title' => 'S3 Storage Settings',
    's3_active' => 'S3 Active',
    'local_storage' => 'Local Storage',
    'description' => 'Configure your storage driver. Choose between local file storage or S3-compatible cloud storage (AWS S3, MinIO, DigitalOcean Spaces, etc.)',
    'storage_driver' => 'Storage Driver',
    's3_compatible_storage' => 'S3 Compatible Storage',
    's3_configuration' => 'S3 Configuration',
    'bucket_name' => 'Bucket Name',
    'endpoint_url' => 'Endpoint URL',
    'endpoint_help' => 'Custom endpoint for S3-compatible services like MinIO or DigitalOcean Spaces. Leave empty for AWS S3.',
    'custom_url' => 'Custom URL',
    'custom_url_help' => 'Custom URL for accessing files (e.g., CloudFront CDN URL). Leave empty to use default S3 URL.',
    'root_path' => 'Root Path',
    'root_path_help' => 'Root directory inside the bucket. All files will be stored under this path.',
    'test_connection' => 'Test Connection',
    'save_settings' => 'Save Settings',
    'back' => 'Back',
    'testing' => 'Testing...',
    'error_occurred' => 'An error occurred.',
];
