<?php

namespace Parvez\GalleryManager\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Parvez\GalleryManager\Models\Media;
use Parvez\GalleryManager\Models\Folder;
use Parvez\GalleryManager\Enums\FileType;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class MediaService
{
    protected ?ImageManager $imageManager = null;

    public function __construct()
    {
        if (class_exists(ImageManager::class)) {
            $this->imageManager = new ImageManager(new Driver());
        }
    }

    public function upload(UploadedFile $file, ?int $userId = null, array $options = []): Media
    {
        $disk = config('gallery-manager.disk', 'public');
        $basePath = config('gallery-manager.storage_path', 'media');
        
        // Determine file type
        $mimeType = $file->getMimeType();
        $type = $this->determineFileType($mimeType);
        
        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        
        // Create path structure
        $folderPath = $this->createFolderPath($userId, $options['folder_id'] ?? null);
        $filePath = "{$basePath}/{$folderPath}/{$filename}";
        
        // Store file
        Storage::disk($disk)->put($filePath, file_get_contents($file->getRealPath()));
        
        // Get file dimensions and duration
        $width = 0;
        $height = 0;
        $duration = 0;
        
        if ($type === FileType::IMAGE && $this->imageManager) {
            try {
                $image = $this->imageManager->read($file->getRealPath());
                $width = $image->width();
                $height = $image->height();
            } catch (\Exception $e) {
                // Skip if image processing fails
            }
        }
        
        // Create media record
        $media = Media::create([
            'user_id' => $userId,
            'original' => $file->getClientOriginalName(),
            'name' => $options['name'] ?? pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME),
            'type' => $type,
            'path' => $filePath,
            'folder_id' => $options['folder_id'] ?? null,
            'url' => Storage::disk($disk)->url($filePath),
            'mime_type' => $mimeType,
            'extension' => $extension,
            'size' => $file->getSize(),
            'width' => $width,
            'height' => $height,
            'duration' => $duration,
        ]);
        
        // Attach tags
        if (!empty($options['tags'])) {
            $media->tags()->sync($options['tags']);
        }
        
        return $media->fresh(['tags', 'folder']);
    }

    protected function determineFileType(string $mimeType): FileType
    {
        if (str_starts_with($mimeType, 'image/')) {
            return FileType::IMAGE;
        } elseif (str_starts_with($mimeType, 'video/')) {
            return FileType::VIDEO;
        } elseif (str_starts_with($mimeType, 'audio/')) {
            return FileType::AUDIO;
        } elseif (in_array($mimeType, ['application/pdf', 'application/msword', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])) {
            return FileType::DOCUMENT;
        }
        
        return FileType::OTHER;
    }

    protected function createFolderPath(?int $userId, ?int $folderId): string
    {
        if ($folderId) {
            $folder = Folder::find($folderId);
            if ($folder) {
                return "folders/{$folderId}";
            }
        }
        
        if ($userId) {
            return "users/{$userId}";
        }
        
        return 'public';
    }

    public function getMedia(?int $userId = null, array $filters = [])
    {
        $query = Media::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if (!empty($filters['folder_id'])) {
            $query->where('folder_id', $filters['folder_id']);
        }
        
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['search']}%")
                  ->orWhere('original', 'like', "%{$filters['search']}%");
            });
        }
        
        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('tags.id', $filters['tags']);
            });
        }
        
        return $query->with(['tags', 'folder'])
            ->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function updateMedia(int $mediaId, array $data): Media
    {
        $media = Media::findOrFail($mediaId);
        $media->update($data);
        
        if (isset($data['tags'])) {
            $media->tags()->sync($data['tags']);
        }
        
        return $media->fresh(['tags', 'folder']);
    }

    public function deleteMedia(int $mediaId): bool
    {
        $media = Media::findOrFail($mediaId);
        return $media->delete();
    }

    public function createFolder(string $name, ?int $userId = null, ?int $parentId = null): Folder
    {
        return Folder::create([
            'name' => $name,
            'user_id' => $userId,
            'parent_id' => $parentId,
        ]);
    }

    public function getFolders(?int $userId = null, ?int $parentId = null)
    {
        $query = Folder::query();
        
        if ($userId) {
            $query->where('user_id', $userId);
        }
        
        if ($parentId === null) {
            $query->whereNull('parent_id');
        } else {
            $query->where('parent_id', $parentId);
        }
        
        return $query->withCount('media')
            ->with('children')
            ->orderBy('name')
            ->get();
    }

    public function updateFolder(int $folderId, array $data): Folder
    {
        $folder = Folder::findOrFail($folderId);
        $folder->update($data);
        
        return $folder->fresh(['parent', 'children']);
    }

    public function deleteFolder(int $folderId): bool
    {
        $folder = Folder::findOrFail($folderId);
        
        // Delete all media in folder
        $folder->media()->each(function ($media) {
            $media->delete();
        });
        
        // Delete all subfolders recursively
        $folder->children()->each(function ($child) {
            $this->deleteFolder($child->id);
        });
        
        return $folder->delete();
    }
}
