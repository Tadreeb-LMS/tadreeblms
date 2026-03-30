<?php

return [
    'page_title' => 'Configuracion de almacenamiento S3',
    's3_active' => 'S3 activo',
    'local_storage' => 'Almacenamiento local',
    'description' => 'Configura el controlador de almacenamiento. Elige entre almacenamiento local o nube compatible con S3 (AWS S3, MinIO, DigitalOcean Spaces, etc.).',
    'storage_driver' => 'Controlador de almacenamiento',
    's3_compatible_storage' => 'Almacenamiento compatible con S3',
    's3_configuration' => 'Configuracion S3',
    'bucket_name' => 'Nombre del bucket',
    'endpoint_url' => 'URL de endpoint',
    'endpoint_help' => 'Endpoint personalizado para servicios compatibles con S3 como MinIO o DigitalOcean Spaces. Dejalo vacio para AWS S3.',
    'custom_url' => 'URL personalizada',
    'custom_url_help' => 'URL personalizada para acceder a archivos (ej. CloudFront CDN). Dejalo vacio para usar la URL predeterminada de S3.',
    'root_path' => 'Ruta raiz',
    'root_path_help' => 'Directorio raiz dentro del bucket. Todos los archivos se guardaran en esta ruta.',
    'test_connection' => 'Probar conexion',
    'save_settings' => 'Guardar configuracion',
    'back' => 'Volver',
    'testing' => 'Probando...',
    'error_occurred' => 'Ocurrio un error.',
];
