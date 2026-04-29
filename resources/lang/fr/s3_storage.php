<?php

return [
    'page_title' => 'Parametres de stockage S3',
    's3_active' => 'S3 actif',
    'local_storage' => 'Stockage local',
    'description' => 'Configurez le pilote de stockage. Choisissez entre stockage local ou stockage cloud compatible S3 (AWS S3, MinIO, DigitalOcean Spaces, etc.).',
    'storage_driver' => 'Pilote de stockage',
    's3_compatible_storage' => 'Stockage compatible S3',
    's3_configuration' => 'Configuration S3',
    'bucket_name' => 'Nom du bucket',
    'endpoint_url' => 'URL endpoint',
    'endpoint_help' => 'Endpoint personnalise pour des services compatibles S3 comme MinIO ou DigitalOcean Spaces. Laissez vide pour AWS S3.',
    'custom_url' => 'URL personnalisee',
    'custom_url_help' => 'URL personnalisee pour acceder aux fichiers (ex. CloudFront CDN). Laissez vide pour utiliser l\'URL S3 par defaut.',
    'root_path' => 'Chemin racine',
    'root_path_help' => 'Repertoire racine dans le bucket. Tous les fichiers seront stockes sous ce chemin.',
    'test_connection' => 'Tester la connexion',
    'save_settings' => 'Enregistrer les parametres',
    'back' => 'Retour',
    'testing' => 'Test en cours...',
    'error_occurred' => 'Une erreur est survenue.',
];
