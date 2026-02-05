# Laravel Gallery Manager

A comprehensive Laravel package for managing media files (images, videos, documents, audio) with hierarchical folder structure. Supports both local and AWS S3 storage.

## Features

- 📁 **Hierarchical Folder Structure** - Organize media with parent-child folder relationships
- 🎨 **Multi-File Type Support** - Images, Videos, Documents, Audio, and more
- 🏷️ **Tagging System** - Tag and categorize your media files
- ☁️ **Cloud Storage** - Support for local, public, and AWS S3 storage
- 🔐 **User Management** - Optional user-specific media organization
- 🗑️ **Soft Deletes** - Safely delete and restore media and folders
- 📊 **Auto-calculated Metrics** - Automatic folder size and media count
- 🔍 **Search & Filter** - Search by name, type, tags, and folders
- 🎯 **RESTful API** - Complete API for media and folder management
- 📦 **Easy Integration** - Simple installation and configuration

## Requirements

- PHP >= 8.1
- Laravel >= 10.0
- intervention/image ^3.0 (optional, for image processing)

## Installation

```bash
composer require parvez/laravel-gallery-manager
```

### Publish Configuration

```bash
php artisan vendor:publish --tag=gallery-config
```

### Publish Migrations

```bash
php artisan vendor:publish --tag=gallery-migrations
php artisan migrate
```

### Publish Vue Components (Optional)

```bash
php artisan vendor:publish --tag=gallery-components
php artisan vendor:publish --tag=gallery-css
```

## Configuration

Edit `config/gallery-manager.php`:

```php
return [
    'disk' => env('GALLERY_DISK', 'public'),
    'storage_path' => env('GALLERY_STORAGE_PATH', 'media'),
    'max_size' => env('GALLERY_MAX_SIZE', 10240), // KB
    
    'allowed_mimes' => [
        // Images, Videos, Documents, Audio
        'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
        'mp4', 'avi', 'mov', 'wmv',
        'pdf', 'doc', 'docx', 'xls', 'xlsx',
        'mp3', 'wav', 'ogg',
    ],
    
    'routes' => [
        'prefix' => 'api/gallery',
        'middleware' => ['api', 'auth:sanctum'],
    ],
];
```

## Database Schema

### Tables

**media** - Stores media files
- `id`, `user_id`, `original`, `name`, `type` (enum: image, video, document, audio, other)
- `path`, `folder_id`, `url`, `mime_type`, `extension`
- `size`, `width`, `height`, `duration`
- `created_at`, `updated_at`, `deleted_at`

**folders** - Hierarchical folder structure
- `id`, `name`, `user_id`, `parent_id`
- `created_at`, `updated_at`, `deleted_at`

**tags** - Media tags
- `id`, `name`, `slug`
- `created_at`, `updated_at`

**media_tag** - Pivot table for media-tag relationships

## API Endpoints

### Media Endpoints

```http
GET    /api/gallery/media              # List media with filters
POST   /api/gallery/media              # Upload media file(s)
GET    /api/gallery/media/{id}         # Get media details
PUT    /api/gallery/media/{id}         # Update media
DELETE /api/gallery/media/{id}         # Delete media
GET    /api/gallery/media/{id}/download # Download media
POST   /api/gallery/media/bulk-delete  # Bulk delete media
```

### Folder Endpoints

```http
GET    /api/gallery/folders            # List folders
POST   /api/gallery/folders            # Create folder
GET    /api/gallery/folders/{id}       # Get folder details
PUT    /api/gallery/folders/{id}       # Update folder
DELETE /api/gallery/folders/{id}       # Delete folder (recursive)
```

## Usage Examples

### Upload Media

```php
use Parvez\GalleryManager\Services\MediaService;

$mediaService = app(MediaService::class);

$media = $mediaService->upload($request->file('file'), auth()->id(), [
    'name' => 'My Photo',
    'folder_id' => 1,
    'tags' => [1, 2, 3],
]);
```

### Create Folder

```php
$folder = $mediaService->createFolder('Photos', auth()->id(), $parentId = null);
```

### Get Media with Filters

```php
$media = $mediaService->getMedia(auth()->id(), [
    'folder_id' => 1,
    'type' => 'image',
    'search' => 'vacation',
    'tags' => [1, 2],
    'per_page' => 20,
]);
```

### Using Facade

```php
use Parvez\GalleryManager\Facades\Gallery;

$media = Gallery::upload($file, $userId);
```

## Models

### Media Model

```php
use Parvez\GalleryManager\Models\Media;

// Relationships
$media->user();
$media->folder();
$media->tags();

// Helper Methods
$media->isImage();
$media->isVideo();
$media->isDocument();
$media->isAudio();

// Attributes
$media->formatted_size; // e.g., "2.5 MB"
$media->url; // Full URL to the file
```

### Folder Model

```php
use Parvez\GalleryManager\Models\Folder;

// Relationships
$folder->user();
$folder->parent();
$folder->children();
$folder->media();

// Computed Attributes
$folder->media_count;
$folder->total_size;
$folder->formatted_size;

// Helper Methods
$folder->getBreadcrumb(); // Get full path hierarchy
```

### Tag Model

```php
use Parvez\GalleryManager\Models\Tag;

$tag->media(); // Get all media with this tag
```

## Frontend Integration

Upload media with JavaScript:

```javascript
const formData = new FormData();
formData.append('file', fileInput.files[0]);
formData.append('name', 'My File');
formData.append('folder_id', 1);
formData.append('tags', JSON.stringify([1, 2]));

await axios.post('/api/gallery/media', formData, {
    headers: { 'Content-Type': 'multipart/form-data' }
});
```

## File Type Detection

The package automatically detects file types based on MIME type:

- **Images**: `image/*`
- **Videos**: `video/*`
- **Audio**: `audio/*`
- **Documents**: PDF, Word, Excel, PowerPoint, etc.
- **Other**: Everything else

## Authorization

The package includes basic authorization checks. Media and folders can be:
- **User-specific**: `user_id` is set (only owner can access)
- **Public**: `user_id` is null (anyone can access)

You can customize authorization in the `MediaController`.

## Testing

```bash
composer test
```

## Security

- File size limits
- MIME type validation
- User authorization checks
- Soft deletes for safe data management

## Contributing

Contributions are welcome! Please submit PRs or open issues.

## License

MIT License. See [LICENSE](LICENSE) for details.

## Credits

- **Author**: Parvez Rahman
- **Email**: parvezrohmanfci@gmail.com

## Changelog

### Version 2.0.0

- ✅ Complete rewrite with new database schema
- ✅ Multi-file type support (images, videos, documents, audio)
- ✅ Hierarchical folder structure
- ✅ Improved API endpoints
- ✅ Better resource transformers
- ✅ Simplified configuration
- ✅ Enhanced models with relationships

### Version 1.x

- Basic image gallery functionality
- Date-based organization
- Local and S3 storage

## Support

For issues and questions:
- Open an issue on GitHub
- Email: parvezrohmanfci@gmail.com
