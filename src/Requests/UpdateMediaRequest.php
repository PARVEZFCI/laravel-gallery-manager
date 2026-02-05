<?php

namespace Parvez\GalleryManager\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Parvez\GalleryManager\Enums\FileType;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|string|in:' . implode(',', FileType::getValues()),
            'folder_id' => 'sometimes|nullable|integer|exists:folders,id',
            'tags' => 'sometimes|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }
}
