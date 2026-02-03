<?php

namespace Parvez\GalleryManager\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string|max:1000',
            'tags' => 'sometimes|array',
            'tags.*' => 'integer|exists:gallery_tags,id',
            'is_public' => 'sometimes|boolean',
        ];
    }
}
