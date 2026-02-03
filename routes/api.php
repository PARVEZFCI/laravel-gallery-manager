<?php

use Illuminate\Support\Facades\Route;
use Parvez\GalleryManager\Controllers\GalleryController;

$config = config('gallery-manager.routes');

Route::prefix($config['prefix'])
    ->middleware($config['middleware'])
    ->group(function () {
        // Gallery folders
        Route::get('/folders', [GalleryController::class, 'getFolders'])->name('gallery.folders.index');
        
        // Gallery images
        Route::get('/images', [GalleryController::class, 'getImages'])->name('gallery.images.index');
        Route::post('/images/upload', [GalleryController::class, 'upload'])->name('gallery.images.upload');
        Route::get('/images/{id}', [GalleryController::class, 'show'])->name('gallery.images.show');
        Route::put('/images/{id}', [GalleryController::class, 'update'])->name('gallery.images.update');
        Route::delete('/images/{id}', [GalleryController::class, 'destroy'])->name('gallery.images.destroy');
        Route::get('/images/{id}/download', [GalleryController::class, 'download'])->name('gallery.images.download');
        
        // Bulk operations
        Route::post('/images/bulk-delete', [GalleryController::class, 'bulkDelete'])->name('gallery.images.bulk-delete');
    });

// Inertia routes (if using web middleware)
Route::middleware(['web', 'auth'])
    ->group(function () {
        Route::get('/gallery', [GalleryController::class, 'index'])->name('gallery.index');
    });
