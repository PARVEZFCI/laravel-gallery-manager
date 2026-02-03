<?php

namespace Parvez\GalleryManager\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Parvez\GalleryManager\Models\GalleryImage;
use Parvez\GalleryManager\Models\GalleryFolder;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;
use Carbon\Carbon;

class GalleryService
{
    protected ImageManager $imageManager;

    public function __construct()
    {
        $this->imageManager = new ImageManager(new Driver());
    }

    public function upload(UploadedFile $file, int $userId, array $options = []): GalleryImage
    {
        // Validate file
        $this->validateFile($file);

        // Generate paths
        $date = $options['date'] ?? now()->format('Y-m-d');
        $folderDate = Carbon::parse($date)->format('Y-m-d');
        $datePath = Carbon::parse($date)->format(config('gallery-manager.date_format', 'Y/m/d'));
        
        $disk = $options['disk'] ?? config('gallery-manager.disk', 'public');
        $basePath = config('gallery-manager.storage_path', 'gallery');
        $organization = config('gallery-manager.organization', 'user-date');

        // Create folder structure
        if ($organization === 'user-date') {
            $folderPath = "{$basePath}/users/{$userId}/{$datePath}";
        } else {
            $folderPath = "{$basePath}/{$datePath}/users/{$userId}";
        }

        // Generate unique filename
        $extension = $file->getClientOriginalExtension();
        $filename = Str::uuid() . '.' . $extension;
        $filePath = "{$folderPath}/{$filename}";

        // Store original file
        Storage::disk($disk)->put($filePath, file_get_contents($file->getRealPath()));

        // Get image dimensions
        $image = $this->imageManager->read($file->getRealPath());
        $metadata = [
            'width' => $image->width(),
            'height' => $image->height(),
        ];

        // Create thumbnail and medium versions
        $thumbnailPath = null;
        $mediumPath = null;

        if (config('gallery-manager.image.thumbnail.enabled', true)) {
            $thumbnailPath = $this->createThumbnail($file, $folderPath, $filename, $disk);
        }

        if (config('gallery-manager.image.medium.enabled', true)) {
            $mediumPath = $this->createMedium($file, $folderPath, $filename, $disk);
        }

        // Create database record
        $galleryImage = GalleryImage::create([
            'user_id' => $userId,
            'title' => $options['title'] ?? $file->getClientOriginalName(),
            'description' => $options['description'] ?? null,
            'filename' => $filename,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'disk' => $disk,
            'path' => $filePath,
            'thumbnail_path' => $thumbnailPath,
            'medium_path' => $mediumPath,
            'metadata' => $metadata,
            'folder_date' => $folderDate,
            'is_public' => $options['is_public'] ?? true,
            'uploaded_at' => now(),
        ]);

        // Update folder stats
        $this->updateFolderStats($userId, $folderDate, $folderPath, $file->getSize(), 1);

        // Add tags if provided
        if (!empty($options['tags'])) {
            $galleryImage->tags()->sync($options['tags']);
        }

        return $galleryImage->fresh(['tags']);
    }

    protected function validateFile(UploadedFile $file): void
    {
        $maxSize = config('gallery-manager.image.max_size', 5120) * 1024; // Convert KB to bytes
        $allowedExtensions = config('gallery-manager.image.allowed_extensions', ['jpg', 'jpeg', 'png', 'gif', 'webp']);

        if ($file->getSize() > $maxSize) {
            throw new \Exception("File size exceeds maximum allowed size of " . ($maxSize / 1024) . "KB");
        }

        $extension = strtolower($file->getClientOriginalExtension());
        if (!in_array($extension, $allowedExtensions)) {
            throw new \Exception("File type not allowed. Allowed types: " . implode(', ', $allowedExtensions));
        }
    }

    protected function createThumbnail(UploadedFile $file, string $folderPath, string $filename, string $disk): string
    {
        $thumbnailWidth = config('gallery-manager.image.thumbnail.width', 300);
        $thumbnailHeight = config('gallery-manager.image.thumbnail.height', 300);

        $image = $this->imageManager->read($file->getRealPath());
        $image->cover($thumbnailWidth, $thumbnailHeight);

        $thumbnailFilename = 'thumb_' . $filename;
        $thumbnailPath = "{$folderPath}/thumbnails/{$thumbnailFilename}";

        Storage::disk($disk)->put($thumbnailPath, $image->encode());

        return $thumbnailPath;
    }

    protected function createMedium(UploadedFile $file, string $folderPath, string $filename, string $disk): string
    {
        $mediumWidth = config('gallery-manager.image.medium.width', 800);
        $mediumHeight = config('gallery-manager.image.medium.height', 800);

        $image = $this->imageManager->read($file->getRealPath());
        
        // Resize maintaining aspect ratio
        $image->scale(width: $mediumWidth, height: $mediumHeight);

        $mediumFilename = 'medium_' . $filename;
        $mediumPath = "{$folderPath}/medium/{$mediumFilename}";

        Storage::disk($disk)->put($mediumPath, $image->encode());

        return $mediumPath;
    }

    protected function updateFolderStats(int $userId, string $folderDate, string $folderPath, int $sizeChange, int $countChange): void
    {
        $folder = GalleryFolder::firstOrCreate(
            [
                'user_id' => $userId,
                'folder_date' => $folderDate,
            ],
            [
                'folder_path' => $folderPath,
                'image_count' => 0,
                'total_size' => 0,
            ]
        );

        $folder->increment('total_size', $sizeChange);
        $folder->increment('image_count', $countChange);
    }

    public function getFolders(int $userId, array $filters = [])
    {
        $query = GalleryFolder::where('user_id', $userId);

        if (!empty($filters['date_from'])) {
            $query->where('folder_date', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->where('folder_date', '<=', $filters['date_to']);
        }

        return $query->orderBy('folder_date', 'desc')->get();
    }

    public function getImages(int $userId, array $filters = [])
    {
        $query = GalleryImage::where('user_id', $userId);

        if (!empty($filters['folder_date'])) {
            $query->where('folder_date', $filters['folder_date']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'like', "%{$filters['search']}%")
                  ->orWhere('description', 'like', "%{$filters['search']}%");
            });
        }

        if (!empty($filters['tags'])) {
            $query->whereHas('tags', function ($q) use ($filters) {
                $q->whereIn('gallery_tags.id', $filters['tags']);
            });
        }

        return $query->with('tags')
            ->orderBy('uploaded_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function updateImage(int $imageId, array $data): GalleryImage
    {
        $image = GalleryImage::findOrFail($imageId);
        $image->update($data);

        if (!empty($data['tags'])) {
            $image->tags()->sync($data['tags']);
        }

        return $image->fresh(['tags']);
    }

    public function deleteImage(int $imageId): bool
    {
        $image = GalleryImage::findOrFail($imageId);
        return $image->delete();
    }

    public function downloadImage(int $imageId): array
    {
        $image = GalleryImage::findOrFail($imageId);
        
        return [
            'content' => Storage::disk($image->disk)->get($image->path),
            'filename' => $image->original_filename,
            'mime_type' => $image->mime_type,
        ];
    }
}
