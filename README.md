# Laravel Gallery Manager

A comprehensive Laravel package for managing images with date-wise and user-wise organization, supporting both local and AWS S3 storage. Perfect for applications built with Inertia.js and Vue 3.

## Features

- 📁 **Automatic folder organization** - Date-wise and user-wise folder structure
- ☁️ **AWS S3 Support** - Store images on AWS S3 or local storage
- 🖼️ **Multiple image sizes** - Automatic thumbnail and medium size generation
- 📱 **Vue 3 + Inertia.js** - Beautiful, reactive UI components
- ✏️ **Full CRUD operations** - Create, read, update, delete images
- 🏷️ **Tagging system** - Organize images with tags
- 📥 **Download support** - Download original images
- 🗑️ **Bulk operations** - Delete multiple images at once
- 🔍 **Search & filter** - Find images quickly
- 📊 **Folder statistics** - Track image counts and storage sizes

## Requirements

- PHP 8.0 or higher
- Laravel 9.x, 10.x or 11.x
- Vue 3
- Inertia.js
- Intervention Image 3.x

## Installation

### Step 1: Install Package via Composer

```bash
composer require parvez/laravel-gallery-manager
```

### Step 2: Publish Configuration and Assets

```bash
# Publish config file
php artisan vendor:publish --tag=gallery-config

# Publish migrations
php artisan vendor:publish --tag=gallery-migrations

# Publish Vue components
php artisan vendor:publish --tag=gallery-components

# Publish CSS (optional)
php artisan vendor:publish --tag=gallery-css
```

### Step 3: Run Migrations

```bash
php artisan migrate
```

### Step 4: Configure Storage

#### For Local Storage

Update your `.env` file:

```env
GALLERY_DISK=public
GALLERY_STORAGE_PATH=gallery
```

Create symbolic link:

```bash
php artisan storage:link
```

#### For AWS S3 Storage

Install AWS SDK:

```bash
composer require league/flysystem-aws-s3-v3 "^3.0"
```

Update your `.env` file:

```env
GALLERY_DISK=s3
AWS_ACCESS_KEY_ID=your-access-key
AWS_SECRET_ACCESS_KEY=your-secret-key
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=your-bucket-name
AWS_URL=https://your-bucket.s3.amazonaws.com
```

Update `config/filesystems.php`:

```php
's3' => [
    'driver' => 's3',
    'key' => env('AWS_ACCESS_KEY_ID'),
    'secret' => env('AWS_SECRET_ACCESS_KEY'),
    'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    'bucket' => env('AWS_BUCKET'),
    'url' => env('AWS_URL'),
    'visibility' => 'public',
],
```

### Step 5: Install Vue Components

The package publishes Vue components to `resources/js/vendor/gallery-manager/`. You need to import them in your project.

#### Option 1: Auto-import (Recommended)

If using Vite, add to `vite.config.js`:

```javascript
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
    ],
    resolve: {
        alias: {
            '@gallery': '/resources/js/vendor/gallery-manager',
        },
    },
});
```

#### Option 2: Manual Import

In your route file (e.g., `routes/web.php`):

```php
use Inertia\Inertia;
use YourName\GalleryManager\Controllers\GalleryController;

Route::middleware(['auth'])->group(function () {
    Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
});
```

### Step 6: Update Navigation (Optional)

Add gallery link to your navigation:

```vue
<Link href="/gallery">Gallery</Link>
```

## Configuration

All configuration options are in `config/gallery-manager.php`:

```php
return [
    // Storage disk: 'public', 's3', 'local'
    'disk' => env('GALLERY_DISK', 'public'),
    
    // Base storage path
    'storage_path' => env('GALLERY_STORAGE_PATH', 'gallery'),
    
    // Date format for folders: 'Y-m-d', 'Y/m/d', 'Y-m'
    'date_format' => env('GALLERY_DATE_FORMAT', 'Y/m/d'),
    
    // Organization structure: 'user-date' or 'date-user'
    // user-date: gallery/users/{user_id}/{date}/
    // date-user: gallery/{date}/users/{user_id}/
    'organization' => env('GALLERY_ORGANIZATION', 'user-date'),
    
    // Image settings
    'image' => [
        'max_size' => 5120, // KB
        'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
        'quality' => 90,
        
        'thumbnail' => [
            'enabled' => true,
            'width' => 300,
            'height' => 300,
        ],
        
        'medium' => [
            'enabled' => true,
            'width' => 800,
            'height' => 800,
        ],
    ],
    
    // API routes configuration
    'routes' => [
        'prefix' => 'api/gallery',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];
```

## Usage

### Basic Usage via Facade

```php
use Parvez\GalleryManager\Facades\Gallery;

// Upload image
$image = Gallery::upload($file, $userId, [
    'title' => 'My Image',
    'description' => 'Image description',
    'tags' => [1, 2, 3],
    'date' => '2024-01-15',
    'disk' => 's3',
]);

// Get user folders
$folders = Gallery::getFolders($userId);

// Get images
$images = Gallery::getImages($userId, [
    'folder_date' => '2024-01-15',
    'search' => 'keyword',
    'tags' => [1, 2],
    'per_page' => 20,
]);

// Update image
$image = Gallery::updateImage($imageId, [
    'title' => 'Updated Title',
    'description' => 'Updated Description',
]);

// Delete image
Gallery::deleteImage($imageId);

// Download image
$fileData = Gallery::downloadImage($imageId);
```

### API Endpoints

All endpoints are protected by authentication middleware:

**Get Folders**
```
GET /api/gallery/folders
```

**Get Images**
```
GET /api/gallery/images?folder_date=2024-01-15&search=keyword
```

**Upload Images**
```
POST /api/gallery/images/upload
Content-Type: multipart/form-data

Parameters:
- images[] or image (file)
- title (optional)
- description (optional)
- tags[] (optional, array of tag IDs)
- date (optional, defaults to today)
- disk (optional, 'public' or 's3')
```

**Get Single Image**
```
GET /api/gallery/images/{id}
```

**Update Image**
```
PUT /api/gallery/images/{id}
Content-Type: application/json

{
    "title": "Updated Title",
    "description": "Updated Description",
    "tags": [1, 2, 3]
}
```

**Delete Image**
```
DELETE /api/gallery/images/{id}
```

**Download Image**
```
GET /api/gallery/images/{id}/download
```

**Bulk Delete**
```
POST /api/gallery/images/bulk-delete
Content-Type: application/json

{
    "image_ids": [1, 2, 3]
}
```

### Using Vue Components

The package provides ready-to-use Vue 3 components:

```vue
<script setup>
import GalleryIndex from '@gallery/Pages/GalleryIndex.vue'
</script>

<template>
  <GalleryIndex :folders="folders" />
</template>
```

Available components:
- `GalleryIndex.vue` - Main gallery page
- `GalleryFolderCard.vue` - Folder display card
- `GalleryImageGrid.vue` - Image grid display
- `GalleryUploadModal.vue` - Upload modal dialog
- `GalleryImageModal.vue` - Image detail modal

### Working with Tags

```php
use Parvez\GalleryManager\Models\GalleryTag;

// Create tags
$tag = GalleryTag::create(['name' => 'Nature']);

// Attach tags to image
$image->tags()->attach([1, 2, 3]);

// Get images by tag
$images = GalleryImage::whereHas('tags', function($q) {
    $q->where('slug', 'nature');
})->get();
```

### Custom Folder Organization

You can customize how folders are organized:

```php
// In config/gallery-manager.php

// Option 1: User first, then date
'organization' => 'user-date',
// Results in: gallery/users/1/2024/01/15/

// Option 2: Date first, then user
'organization' => 'date-user',
// Results in: gallery/2024/01/15/users/1/
```

### File Size Limits

Configure maximum file size:

```php
// In config/gallery-manager.php
'image' => [
    'max_size' => 5120, // 5MB in KB
    'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'],
],
```

## Advanced Usage

### Custom Image Processing

Extend the `GalleryService` class:

```php
namespace App\Services;

use Parvez\GalleryManager\Services\GalleryService as BaseGalleryService;

class CustomGalleryService extends BaseGalleryService
{
    protected function createThumbnail($file, $folderPath, $filename, $disk)
    {
        // Your custom thumbnail logic
        return parent::createThumbnail($file, $folderPath, $filename, $disk);
    }
}
```

Register in `AppServiceProvider`:

```php
$this->app->singleton('gallery', function ($app) {
    return new \App\Services\CustomGalleryService();
});
```

### Event Listeners

Hook into image events:

```php
use Parvez\GalleryManager\Models\GalleryImage;

GalleryImage::created(function ($image) {
    // Do something when image is uploaded
});

GalleryImage::deleted(function ($image) {
    // Do something when image is deleted
});
```

### Custom Middleware

Add custom middleware to routes:

```php
// In config/gallery-manager.php
'routes' => [
    'prefix' => 'api/gallery',
    'middleware' => ['api', 'auth:sanctum', 'your-custom-middleware'],
],
```

## Folder Structure

The package organizes files like this:

```
storage/app/public/gallery/
├── users/
│   ├── 1/
│   │   ├── 2024/01/15/
│   │   │   ├── image1.jpg
│   │   │   ├── image2.png
│   │   │   ├── thumbnails/
│   │   │   │   ├── thumb_image1.jpg
│   │   │   │   └── thumb_image2.png
│   │   │   └── medium/
│   │   │       ├── medium_image1.jpg
│   │   │       └── medium_image2.png
│   │   └── 2024/01/16/
│   └── 2/
└── ...
```

## Database Structure

### gallery_images
- Stores image metadata
- Links to users
- Contains file paths and sizes
- Soft deletes supported

### gallery_folders
- Tracks folder statistics
- Stores image counts
- Calculates total sizes

### gallery_tags
- Tag management
- Auto-generates slugs

### gallery_image_tag
- Many-to-many relationship
- Links images to tags

## Troubleshooting

### Images not displaying
1. Make sure storage link exists: `php artisan storage:link`
2. Check file permissions
3. Verify disk configuration in `.env`

### Upload failing
1. Check `php.ini` settings: `upload_max_filesize` and `post_max_size`
2. Verify allowed extensions in config
3. Check storage disk has write permissions

### AWS S3 not working
1. Verify AWS credentials in `.env`
2. Check bucket permissions
3. Ensure bucket CORS is configured
4. Install AWS SDK: `composer require league/flysystem-aws-s3-v3`

## Testing

```bash
composer test
```

## Contributing

Contributions are welcome! Please submit pull requests or open issues.

## License

MIT License

## Credits

- [Intervention Image](https://image.intervention.io/)
- [Laravel](https://laravel.com/)
- [Inertia.js](https://inertiajs.com/)
- [Vue 3](https://vuejs.org/)

## Support

For issues and questions:
- Open an issue on GitHub
- Email: parvezrohmanfci@gmail.com

---

Made with ❤️ for the Laravel community
