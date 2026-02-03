<?php

namespace Parvez\GalleryManager\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GalleryFolder extends Model
{
    protected $fillable = [
        'user_id',
        'folder_path',
        'folder_date',
        'image_count',
        'total_size',
    ];

    protected $casts = [
        'image_count' => 'integer',
        'total_size' => 'integer',
    ];

    protected $appends = [
        'formatted_size',
        'formatted_date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(config('gallery-manager.user_model'));
    }

    public function images(): HasMany
    {
        return $this->hasMany(GalleryImage::class, 'folder_date', 'folder_date')
            ->where('user_id', $this->user_id);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->total_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFormattedDateAttribute(): string
    {
        return \Carbon\Carbon::createFromFormat('Y-m-d', $this->folder_date)->format('F d, Y');
    }
}
