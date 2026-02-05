<?php

use Illuminate\Support\Facades\Route;
use Parvez\GalleryManager\Controllers\MediaController;

$config = config('gallery-manager.routes');

Route::prefix($config['prefix'])
    ->middleware($config['middleware'])
    ->group(function () {
        // Media endpoints
        Route::get('/media', [MediaController::class, 'index'])->name('media.index');
        Route::post('/media', [MediaController::class, 'store'])->name('media.store');
        Route::get('/media/{id}', [MediaController::class, 'show'])->name('media.show');
        Route::put('/media/{id}', [MediaController::class, 'update'])->name('media.update');
        Route::delete('/media/{id}', [MediaController::class, 'destroy'])->name('media.destroy');
        Route::get('/media/{id}/download', [MediaController::class, 'download'])->name('media.download');
        Route::post('/media/bulk-delete', [MediaController::class, 'bulkDelete'])->name('media.bulk-delete');
        
        // Folder endpoints
        Route::get('/folders', [MediaController::class, 'getFolders'])->name('folders.index');
        Route::post('/folders', [MediaController::class, 'createFolder'])->name('folders.store');
        Route::get('/folders/{id}', [MediaController::class, 'showFolder'])->name('folders.show');
        Route::put('/folders/{id}', [MediaController::class, 'updateFolder'])->name('folders.update');
        Route::delete('/folders/{id}', [MediaController::class, 'deleteFolder'])->name('folders.destroy');
    });

