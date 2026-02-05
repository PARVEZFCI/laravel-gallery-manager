<?php

namespace Parvez\GalleryManager\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Parvez\GalleryManager\Services\MediaService;
use Parvez\GalleryManager\Requests\UploadMediaRequest;
use Parvez\GalleryManager\Requests\UpdateMediaRequest;
use Parvez\GalleryManager\Requests\CreateFolderRequest;
use Parvez\GalleryManager\Requests\UpdateFolderRequest;
use Parvez\GalleryManager\Resources\MediaResource;
use Parvez\GalleryManager\Resources\FolderResource;
use Parvez\GalleryManager\Models\Media;
use Parvez\GalleryManager\Models\Folder;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    protected MediaService $mediaService;

    public function __construct(MediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    // MEDIA ENDPOINTS

    /**
     * Get all media with filters
     */
    public function index(Request $request)
    {
        $media = $this->mediaService->getMedia(
            $request->user()?->id,
            $request->only(['folder_id', 'type', 'search', 'tags', 'per_page'])
        );

        return MediaResource::collection($media);
    }

    /**
     * Upload single or multiple files
     */
    public function store(UploadMediaRequest $request)
    {
        $uploadedMedia = [];
        $files = $request->hasFile('files') ? $request->file('files') : [$request->file('file')];

        foreach ($files as $file) {
            try {
                $media = $this->mediaService->upload($file, $request->user()?->id, [
                    'name' => $request->input('name'),
                    'folder_id' => $request->input('folder_id'),
                    'tags' => $request->input('tags', []),
                ]);

                $uploadedMedia[] = new MediaResource($media);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedMedia) > 1 ? 'Files uploaded successfully' : 'File uploaded successfully',
            'data' => $uploadedMedia,
        ], 201);
    }

    /**
     * Get single media details
     */
    public function show(Request $request, int $id)
    {
        $media = Media::with(['tags', 'folder'])
            ->findOrFail($id);

        // Check authorization if user-specific
        if ($media->user_id && $request->user() && $media->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return new MediaResource($media);
    }

    /**
     * Update media details
     */
    public function update(UpdateMediaRequest $request, int $id)
    {
        $media = Media::findOrFail($id);

        // Check authorization
        if ($media->user_id && $request->user() && $media->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $updatedMedia = $this->mediaService->updateMedia($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Media updated successfully',
            'data' => new MediaResource($updatedMedia),
        ]);
    }

    /**
     * Delete media
     */
    public function destroy(Request $request, int $id)
    {
        $media = Media::findOrFail($id);

        // Check authorization
        if ($media->user_id && $request->user() && $media->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $this->mediaService->deleteMedia($id);

        return response()->json([
            'success' => true,
            'message' => 'Media deleted successfully',
        ]);
    }

    /**
     * Download media file
     */
    public function download(Request $request, int $id)
    {
        $media = Media::findOrFail($id);

        // Check authorization
        if ($media->user_id && $request->user() && $media->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $disk = config('gallery-manager.disk', 'public');
        
        if (!Storage::disk($disk)->exists($media->path)) {
            abort(404, 'File not found');
        }

        return response()->download(
            Storage::disk($disk)->path($media->path),
            $media->original
        );
    }

    /**
     * Bulk delete media
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'media_ids' => 'required|array',
            'media_ids.*' => 'integer|exists:media,id',
        ]);

        $deletedCount = 0;

        foreach ($request->input('media_ids') as $mediaId) {
            try {
                $media = Media::find($mediaId);

                if ($media && (!$media->user_id || !$request->user() || $media->user_id === $request->user()->id)) {
                    $this->mediaService->deleteMedia($mediaId);
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                // Continue with next media
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} file(s) deleted successfully",
        ]);
    }

    // FOLDER ENDPOINTS

    /**
     * Get all folders
     */
    public function getFolders(Request $request)
    {
        $folders = $this->mediaService->getFolders(
            $request->user()?->id,
            $request->input('parent_id')
        );

        return FolderResource::collection($folders);
    }

    /**
     * Create folder
     */
    public function createFolder(CreateFolderRequest $request)
    {
        $folder = $this->mediaService->createFolder(
            $request->input('name'),
            $request->user()?->id,
            $request->input('parent_id')
        );

        return response()->json([
            'success' => true,
            'message' => 'Folder created successfully',
            'data' => new FolderResource($folder),
        ], 201);
    }

    /**
     * Get single folder details
     */
    public function showFolder(Request $request, int $id)
    {
        $folder = Folder::with(['parent', 'children', 'media'])
            ->findOrFail($id);

        // Check authorization
        if ($folder->user_id && $request->user() && $folder->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        return new FolderResource($folder);
    }

    /**
     * Update folder
     */
    public function updateFolder(UpdateFolderRequest $request, int $id)
    {
        $folder = Folder::findOrFail($id);

        // Check authorization
        if ($folder->user_id && $request->user() && $folder->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $updatedFolder = $this->mediaService->updateFolder($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Folder updated successfully',
            'data' => new FolderResource($updatedFolder),
        ]);
    }

    /**
     * Delete folder
     */
    public function deleteFolder(Request $request, int $id)
    {
        $folder = Folder::findOrFail($id);

        // Check authorization
        if ($folder->user_id && $request->user() && $folder->user_id !== $request->user()->id) {
            abort(403, 'Unauthorized');
        }

        $this->mediaService->deleteFolder($id);

        return response()->json([
            'success' => true,
            'message' => 'Folder deleted successfully',
        ]);
    }
}
