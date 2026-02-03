<?php

namespace Parvez\GalleryManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class GalleryImage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'filename',
        'original_filename',
        'mime_type',
        'size',
        'disk',
        'path',
        'thumbnail_path',
        'medium_path',
        'metadata',
        'folder_date',
        'is_public',
        'uploaded_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'is_public' => 'boolean',
        'uploaded_at' => 'datetime',
        'size' => 'integer',
    ];

    protected $appends = [
        'url',
        'thumbnail_url',
        'medium_url',
        'formatted_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('gallery-manager.user_model'));
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(GalleryTag::class, 'gallery_image_tag');
    }

    public function getUrlAttribute(): ?string
    {
        return $this->path ? Storage::disk($this->disk)->url($this->path) : null;
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        return $this->thumbnail_path ? Storage::disk($this->disk)->url($this->thumbnail_path) : $this->url;
    }

    public function getMediumUrlAttribute(): ?string
    {
        return $this->medium_path ? Storage::disk($this->disk)->url($this->medium_path) : $this->url;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function delete(): ?bool
    {
        // Delete physical files
        if ($this->path) {
            Storage::disk($this->disk)->delete($this->path);
        }
        if ($this->thumbnail_path) {
            Storage::disk($this->disk)->delete($this->thumbnail_path);
        }
        if ($this->medium_path) {
            Storage::disk($this->disk)->delete($this->medium_path);
        }

        // Update folder stats
        $this->updateFolderStats(-$this->size, -1);

        return parent::delete();
    }

    protected function updateFolderStats(int $sizeChange, int $countChange): void
    {
        $folderPath = $this->getFolderPath();
        
        GalleryFolder::updateOrCreate(
            [
                'user_id' => $this->user_id,
                'folder_date' => $this->folder_date,
            ],
            [
                'folder_path' => $folderPath,
            ]
        )->increment('total_size', $sizeChange);

        GalleryFolder::where('user_id', $this->user_id)
            ->where('folder_date', $this->folder_date)
            ->increment('image_count', $countChange);
    }

    protected function getFolderPath(): string
    {
        $basePath = config('gallery-manager.storage_path', 'gallery');
        $organization = config('gallery-manager.organization', 'user-date');
        
        if ($organization === 'user-date') {
            return "{$basePath}/users/{$this->user_id}/{$this->folder_date}";
        } else {
            return "{$basePath}/{$this->folder_date}/users/{$this->user_id}";
        }
    }
}
