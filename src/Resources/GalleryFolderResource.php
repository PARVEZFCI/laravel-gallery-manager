<?php

namespace Parvez\GalleryManager\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GalleryFolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'folder_path' => $this->folder_path,
            'folder_date' => $this->folder_date,
            'formatted_date' => $this->formatted_date,
            'image_count' => $this->image_count,
            'total_size' => $this->total_size,
            'formatted_size' => $this->formatted_size,
            'images' => GalleryImageResource::collection($this->whenLoaded('images')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
