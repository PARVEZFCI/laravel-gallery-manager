<?php

namespace Parvez\GalleryManager\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MediaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'original' => $this->original,
            'name' => $this->name,
            'type' => $this->type?->value,
            'path' => $this->path,
            'folder_id' => $this->folder_id,
            'url' => $this->url,
            'mime_type' => $this->mime_type,
            'extension' => $this->extension,
            'size' => $this->size,
            'formatted_size' => $this->formatted_size,
            'width' => $this->width,
            'height' => $this->height,
            'duration' => $this->duration,
            'folder' => new FolderResource($this->whenLoaded('folder')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
