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
    | Base path for storing gallery images
    |
    */
    'storage_path' => env('GALLERY_STORAGE_PATH', 'gallery'),

    /*
    |--------------------------------------------------------------------------
    | Date Format for Folders
    |--------------------------------------------------------------------------
    |
    | Format: Y-m-d, Y/m/d, Y-m, etc.
    |
    */
    'date_format' => env('GALLERY_DATE_FORMAT', 'Y/m/d'),

    /*
    |--------------------------------------------------------------------------
    | Organization Structure
    |--------------------------------------------------------------------------
    |
    | Options: 'user-date', 'date-user'
    | user-date: gallery/users/{user_id}/{date}/
    | date-user: gallery/{date}/users/{user_id}/
    |
    */
    'organization' => env('GALLERY_ORGANIZATION', 'user-date'),

    /*
    |--------------------------------------------------------------------------
    | Image Processing
    |--------------------------------------------------------------------------
    */
    'image' => [
        'max_size' => env('GALLERY_MAX_SIZE', 5120), // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
        'quality' => env('GALLERY_IMAGE_QUALITY', 90),
        
        // Thumbnail settings
        'thumbnail' => [
            'enabled' => true,
            'width' => 300,
            'height' => 300,
        ],
        
        // Medium size
        'medium' => [
            'enabled' => true,
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
