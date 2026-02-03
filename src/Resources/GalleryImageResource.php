<?php

namespace Parvez\GalleryManager\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'title' => $this->title,
            'description' => $this->description,
            'filename' => $this->filename,
            'original_filename' => $this->original_filename,
            'mime_type' => $this->mime_type,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'disk' => $this->disk,
            'url' => $this->url,
            'thumbnail_url' => $this->thumbnail_url,
            'medium_url' => $this->medium_url,
            'metadata' => $this->metadata,
            'folder_date' => $this->folder_date,
            'is_public' => $this->is_public,
            'tags' => GalleryTagResource::collection($this->whenLoaded('tags')),
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
