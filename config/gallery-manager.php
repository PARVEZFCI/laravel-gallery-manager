<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Storage Disk
    |--------------------------------------------------------------------------
    |
    | Supported: "local", "s3", "public"
    |
    */
    'disk' => env('GALLERY_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | Base path for storing media files
    |
    */
    'storage_path' => env('GALLERY_STORAGE_PATH', 'media'),

    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    */
    'max_size' => env('GALLERY_MAX_SIZE', 10240), // KB (10MB default)
    
    'allowed_mimes' => [
        // Images
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg', 'bmp', 'ico',
        
        // Videos
        'mp4', 'avi', 'mov', 'wmv', 'flv', 'mkv', 'webm',
        
        // Documents
        'pdf', 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx', 'txt', 'csv',
        
        // Audio
        'mp3', 'wav', 'ogg', 'flac', 'aac', 'm4a',
    ],

    /*
    |--------------------------------------------------------------------------
    | Image Processing (requires intervention/image)
    |--------------------------------------------------------------------------
    */
    'image' => [
        'enabled' => true,
        'quality' => env('GALLERY_IMAGE_QUALITY', 90),
        
        // Thumbnail settings
        'thumbnail' => [
            'enabled' => false,
            'width' => 300,
            'height' => 300,
        ],
        
        // Medium size
        'medium' => [
            'enabled' => false,
            'width' => 800,
            'height' => 800,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | AWS S3 Configuration
    |--------------------------------------------------------------------------
    */
    's3' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
        'bucket' => env('AWS_BUCKET'),
        'url' => env('AWS_URL'),
        'endpoint' => env('AWS_ENDPOINT'),
        'visibility' => 'public',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Configuration
    |--------------------------------------------------------------------------
    */
    'routes' => [
        'prefix' => 'api/gallery',
        'middleware' => ['api', 'auth:sanctum'],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model
    |--------------------------------------------------------------------------
    */
    'user_model' => env('GALLERY_USER_MODEL', 'App\Models\User'),

    /*
    |--------------------------------------------------------------------------
    | Permissions
    |--------------------------------------------------------------------------
    */
    'permissions' => [
        'upload' => true,
        'delete' => true,
        'edit' => true,
        'download' => true,
        'view' => true,
    ],
];
