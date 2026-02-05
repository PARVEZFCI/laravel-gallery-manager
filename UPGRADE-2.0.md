# Package Update Summary - Version 2.0.0

## Overview
Complete package restructuring with new database schema, multi-file type support, and hierarchical folder structure.

## Database Changes

### New Schema
- **media** table (replaces gallery_images)
  - Added: `type` enum field (image, video, document, audio, other)
  - Added: `folder_id` foreign key (nullable)
  - Added: `width`, `height`, `duration` fields
  - Added: `extension` field
  - Changed: `user_id` now nullable (supports public media)
  - Removed: Date-based folder organization fields

- **folders** table (replaces gallery_folders)
  - Hierarchical structure with `parent_id`
  - Simple `name` field instead of path-based
  - Computed properties for media_count and total_size
  
- **tags** table (replaces gallery_tags)
  - Simplified naming convention

- **media_tag** pivot table (replaces gallery_image_tag)

## New Files Created

### Models
- `src/Models/Media.php` - Main media model
- `src/Models/Folder.php` - Hierarchical folder model
- `src/Models/Tag.php` - Tag model
- `src/Enums/FileType.php` - File type enum

### Controllers
- `src/Controllers/MediaController.php` - Handles all media and folder operations

### Services
- `src/Services/MediaService.php` - Business logic for media management

### Resources (API Transformers)
- `src/Resources/MediaResource.php`
- `src/Resources/FolderResource.php`
- `src/Resources/TagResource.php`

### Request Validators
- `src/Requests/UploadMediaRequest.php`
- `src/Requests/UpdateMediaRequest.php`
- `src/Requests/CreateFolderRequest.php`
- `src/Requests/UpdateFolderRequest.php`

## Files Removed
- `src/Models/GalleryImage.php`
- `src/Models/GalleryFolder.php`
- `src/Models/GalleryTag.php`
- `src/Controllers/GalleryController.php`
- `src/Services/GalleryService.php`
- `src/Resources/GalleryImageResource.php`
- `src/Resources/GalleryFolderResource.php`
- `src/Resources/GalleryTagResource.php`
- `src/Requests/UploadImageRequest.php`
- `src/Requests/UpdateImageRequest.php`

## Updated Files
- `database/migrations/2024_01_01_000000_create_gallery_tables.php` - New schema
- `routes/api.php` - Updated endpoints
- `src/GalleryManagerServiceProvider.php` - Updated service binding
- `config/gallery-manager.php` - Simplified configuration
- `composer.json` - Updated description and requirements
- `README.md` - Complete documentation rewrite

## API Changes

### Old Endpoints (v1.x)
```
GET  /api/gallery/folders
GET  /api/gallery/images
POST /api/gallery/images/upload
```

### New Endpoints (v2.0)
```
# Media
GET    /api/gallery/media
POST   /api/gallery/media
GET    /api/gallery/media/{id}
PUT    /api/gallery/media/{id}
DELETE /api/gallery/media/{id}
GET    /api/gallery/media/{id}/download
POST   /api/gallery/media/bulk-delete

# Folders
GET    /api/gallery/folders
POST   /api/gallery/folders
GET    /api/gallery/folders/{id}
PUT    /api/gallery/folders/{id}
DELETE /api/gallery/folders/{id}
```

## Feature Additions

1. **Multi-File Type Support**
   - Images (jpg, png, gif, webp, svg)
   - Videos (mp4, avi, mov, wmv)
   - Documents (pdf, doc, docx, xls, xlsx)
   - Audio (mp3, wav, ogg)
   - Other file types

2. **Hierarchical Folders**
   - Parent-child relationships
   - Breadcrumb navigation
   - Recursive deletion

3. **Enhanced Media Model**
   - Type checking methods (isImage(), isVideo(), etc.)
   - Automatic file type detection
   - Dimension tracking (width/height)
   - Duration tracking (for videos/audio)

4. **Improved Authorization**
   - Optional user ownership (nullable user_id)
   - Public media support
   - Per-operation authorization checks

5. **Better Resource Handling**
   - Computed attributes (formatted_size, media_count)
   - Lazy loading relationships
   - Conditional data inclusion

## Configuration Changes

### Removed
- `date_format` - No longer using date-based organization
- `organization` - No longer needed
- `image.max_size` - Moved to top level

### Added
- `max_size` - Global file size limit
- `allowed_mimes` - Expanded list for all file types

### Modified
- `storage_path` - Changed from 'gallery' to 'media'
- `image.thumbnail.enabled` - Default changed to false
- `image.medium.enabled` - Default changed to false

## Migration Path (for existing users)

If you have existing data in v1.x:

1. Backup your database
2. Create a custom migration to transform data:
   ```php
   // Map gallery_images to media
   // Map gallery_folders to folders (create hierarchy)
   // Map gallery_tags to tags
   ```
3. Update your frontend code to use new endpoints
4. Test thoroughly before production deployment

## Breaking Changes

⚠️ **This is a major version upgrade with breaking changes:**

1. Database schema completely changed
2. API endpoints renamed
3. Model names changed
4. Service methods updated
5. Configuration structure modified
6. No backward compatibility with v1.x

## Testing Checklist

- [ ] Upload different file types (image, video, document, audio)
- [ ] Create/update/delete folders
- [ ] Test folder hierarchy (parent-child)
- [ ] Tag management
- [ ] Search and filtering
- [ ] Bulk operations
- [ ] Authorization checks
- [ ] File download
- [ ] Soft delete and restore

## Next Steps

1. Run migrations: `php artisan migrate`
2. Update frontend components (if using)
3. Update API calls to new endpoints
4. Test thoroughly in development
5. Deploy to production with proper backup

## Support

For migration assistance or questions:
- Email: parvezrohmanfci@gmail.com
- Create an issue on GitHub
