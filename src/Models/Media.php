<?php

namespace Parvez\GalleryManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Parvez\GalleryManager\Enums\FileType;

class Media extends Model
{
    use SoftDeletes;

    protected $table = 'media';

    protected $fillable = [
        'user_id',
        'original',
        'name',
        'type',
        'path',
        'folder_id',
        'url',
        'mime_type',
        'extension',
        'size',
        'width',
        'height',
        'duration',
    ];

    protected $casts = [
        'type' => FileType::class,
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
        'duration' => 'integer',
    ];

    protected $appends = [
        'formatted_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('gallery-manager.user_model'));
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(Folder::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'media_tag');
    }

    public function getUrlAttribute($value): ?string
    {
        if ($value) {
            return $value;
        }

        return $this->path ? Storage::disk(config('gallery-manager.disk', 'public'))->url($this->path) : null;
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function isImage(): bool
    {
        return $this->type === FileType::IMAGE;
    }

    public function isVideo(): bool
    {
        return $this->type === FileType::VIDEO;
    }

    public function isDocument(): bool
    {
        return $this->type === FileType::DOCUMENT;
    }

    public function isAudio(): bool
    {
        return $this->type === FileType::AUDIO;
    }

    protected static function boot()
    {
        parent::boot();

        static::deleting(function ($media) {
            // Delete physical file if path exists
            if ($media->path && Storage::disk(config('gallery-manager.disk', 'public'))->exists($media->path)) {
                Storage::disk(config('gallery-manager.disk', 'public'))->delete($media->path);
            }
        });
    }
}
