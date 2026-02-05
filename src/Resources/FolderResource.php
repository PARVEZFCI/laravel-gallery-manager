<?php

namespace Parvez\GalleryManager\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'user_id' => $this->user_id,
            'parent_id' => $this->parent_id,
            'media_count' => $this->when($this->relationLoaded('media'), fn() => $this->media_count),
            'total_size' => $this->when($this->relationLoaded('media'), fn() => $this->total_size),
            'formatted_size' => $this->when($this->relationLoaded('media'), fn() => $this->formatted_size),
            'parent' => new FolderResource($this->whenLoaded('parent')),
            'children' => FolderResource::collection($this->whenLoaded('children')),
            'media' => MediaResource::collection($this->whenLoaded('media')),
            'breadcrumb' => $this->when($request->input('include_breadcrumb'), fn() => $this->getBreadcrumb()),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
        ];
    }
}
