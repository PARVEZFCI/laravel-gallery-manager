<?php

namespace Parvez\GalleryManager\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Parvez\GalleryManager\Services\GalleryService;
use Parvez\GalleryManager\Requests\UploadImageRequest;
use Parvez\GalleryManager\Requests\UpdateImageRequest;
use Parvez\GalleryManager\Resources\GalleryImageResource;
use Parvez\GalleryManager\Resources\GalleryFolderResource;
use Parvez\GalleryManager\Models\GalleryImage;
use Inertia\Inertia;

class GalleryController extends Controller
{
    protected GalleryService $galleryService;

    public function __construct(GalleryService $galleryService)
    {
        $this->galleryService = $galleryService;
    }

    /**
     * Display gallery index page (Inertia)
     */
    public function index(Request $request)
    {
        return Inertia::render('Gallery/Index', [
            'folders' => GalleryFolderResource::collection(
                $this->galleryService->getFolders($request->user()->id)
            ),
        ]);
    }

    /**
     * Get all folders for a user
     */
    public function getFolders(Request $request)
    {
        $folders = $this->galleryService->getFolders(
            $request->user()->id,
            $request->only(['date_from', 'date_to'])
        );

        return GalleryFolderResource::collection($folders);
    }

    /**
     * Get images with filters
     */
    public function getImages(Request $request)
    {
        $images = $this->galleryService->getImages(
            $request->user()->id,
            $request->only(['folder_date', 'search', 'tags', 'per_page'])
        );

        return GalleryImageResource::collection($images);
    }

    /**
     * Upload single or multiple images
     */
    public function upload(UploadImageRequest $request)
    {
        $uploadedImages = [];

        $files = $request->hasFile('images') ? $request->file('images') : [$request->file('image')];

        foreach ($files as $file) {
            try {
                $image = $this->galleryService->upload($file, $request->user()->id, [
                    'title' => $request->input('title'),
                    'description' => $request->input('description'),
                    'tags' => $request->input('tags', []),
                    'date' => $request->input('date'),
                    'disk' => $request->input('disk'),
                    'is_public' => $request->input('is_public', true),
                ]);

                $uploadedImages[] = new GalleryImageResource($image);
            } catch (\Exception $e) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }
        }

        return response()->json([
            'success' => true,
            'message' => count($uploadedImages) > 1 ? 'Images uploaded successfully' : 'Image uploaded successfully',
            'data' => $uploadedImages,
        ], 201);
    }

    /**
     * Get single image details
     */
    public function show(Request $request, int $id)
    {
        $image = GalleryImage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->with('tags')
            ->firstOrFail();

        return new GalleryImageResource($image);
    }

    /**
     * Update image details
     */
    public function update(UpdateImageRequest $request, int $id)
    {
        $image = GalleryImage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $updatedImage = $this->galleryService->updateImage($id, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Image updated successfully',
            'data' => new GalleryImageResource($updatedImage),
        ]);
    }

    /**
     * Delete image
     */
    public function destroy(Request $request, int $id)
    {
        $image = GalleryImage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $this->galleryService->deleteImage($id);

        return response()->json([
            'success' => true,
            'message' => 'Image deleted successfully',
        ]);
    }

    /**
     * Download image
     */
    public function download(Request $request, int $id)
    {
        $image = GalleryImage::where('id', $id)
            ->where('user_id', $request->user()->id)
            ->firstOrFail();

        $fileData = $this->galleryService->downloadImage($id);

        return response($fileData['content'])
            ->header('Content-Type', $fileData['mime_type'])
            ->header('Content-Disposition', 'attachment; filename="' . $fileData['filename'] . '"');
    }

    /**
     * Bulk delete images
     */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'image_ids' => 'required|array',
            'image_ids.*' => 'integer|exists:gallery_images,id',
        ]);

        $deletedCount = 0;

        foreach ($request->input('image_ids') as $imageId) {
            try {
                $image = GalleryImage::where('id', $imageId)
                    ->where('user_id', $request->user()->id)
                    ->first();

                if ($image) {
                    $this->galleryService->deleteImage($imageId);
                    $deletedCount++;
                }
            } catch (\Exception $e) {
                // Continue with next image
            }
        }

        return response()->json([
            'success' => true,
            'message' => "{$deletedCount} image(s) deleted successfully",
        ]);
    }
}
