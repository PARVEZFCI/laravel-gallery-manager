<?php

namespace Parvez\GalleryManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Folder extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'user_id',
        'parent_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'parent_id' => 'integer',
    ];

    protected $appends = [
        'media_count',
        'total_size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('gallery-manager.user_model'));
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Folder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Folder::class, 'parent_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function getMediaCountAttribute(): int
    {
        return $this->media()->count();
    }

    public function getTotalSizeAttribute(): int
    {
        return $this->media()->sum('size');
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->total_size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getBreadcrumb(): array
    {
        $breadcrumb = [];
        $folder = $this;

        while ($folder) {
            array_unshift($breadcrumb, [
                'id' => $folder->id,
                'name' => $folder->name,
            ]);
            $folder = $folder->parent;
        }

        return $breadcrumb;
    }
}
