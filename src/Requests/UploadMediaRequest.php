<?php

namespace Parvez\GalleryManager\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Parvez\GalleryManager\Enums\FileType;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $maxSize = config('gallery-manager.max_size', 10240); // 10MB default
        $allowedMimes = config('gallery-manager.allowed_mimes', [
            'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg',
            'mp4', 'avi', 'mov', 'wmv',
            'pdf', 'doc', 'docx', 'xls', 'xlsx',
            'mp3', 'wav', 'ogg'
        ]);

        return [
            'file' => 'required_without:files|file|mimes:' . implode(',', $allowedMimes) . '|max:' . $maxSize,
            'files' => 'required_without:file|array',
            'files.*' => 'file|mimes:' . implode(',', $allowedMimes) . '|max:' . $maxSize,
            'name' => 'nullable|string|max:255',
            'type' => 'nullable|string|in:' . implode(',', FileType::getValues()),
            'folder_id' => 'nullable|integer|exists:folders,id',
            'tags' => 'nullable|array',
            'tags.*' => 'integer|exists:tags,id',
        ];
    }

    public function messages(): array
    {
        return [
            'file.required_without' => 'Please provide a file',
            'files.required_without' => 'Please provide files',
            'file.mimes' => 'Invalid file type',
            'file.max' => 'File size must not exceed ' . config('gallery-manager.max_size', 10240) . 'KB',
        ];
    }
}
