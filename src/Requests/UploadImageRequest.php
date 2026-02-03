<?php

namespace Parvez\GalleryManager\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('gallery-manager.image.max_size', 5120);
        $extensions = implode(',', config('gallery-manager.image.allowed_extensions', ['jpg', 'jpeg', 'png', 'gif', 'webp']));

        return [
            'image' => 'required_without:images|file|mimes:' . $extensions . '|max:' . $maxSize,
            'images' => 'required_without:image|array',
            'images.*' => 'file|mimes:' . $extensions . '|max:' . $maxSize,
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:1000',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:gallery_tags,id',
            'date' => 'nullable|date',
            'disk' => 'nullable|string|in:local,public,s3',
            'is_public' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'image.required_without' => 'Please provide an image file',
            'images.required_without' => 'Please provide image files',
            'image.mimes' => 'Invalid file type. Allowed types: ' . implode(', ', config('gallery-manager.image.allowed_extensions', [])),
            'image.max' => 'File size must not exceed ' . config('gallery-manager.image.max_size', 5120) . 'KB',
        ];
    }
}
