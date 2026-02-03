<?php

namespace Parvez\GalleryManager;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Parvez\GalleryManager\Services\GalleryService;

class GalleryManagerServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/gallery-manager.php', 'gallery-manager'
        );

        $this->app->singleton('gallery', function ($app) {
            return new GalleryService();
        });
    }

    public function boot()
    {
        // Publish config
        $this->publishes([
            __DIR__.'/../config/gallery-manager.php' => config_path('gallery-manager.php'),
        ], 'gallery-config');

        // Publish migrations
        $this->publishes([
            __DIR__.'/../database/migrations/' => database_path('migrations'),
        ], 'gallery-migrations');

        // Publish Vue components
        $this->publishes([
            __DIR__.'/../resources/js' => resource_path('js/vendor/gallery-manager'),
        ], 'gallery-components');

        // Publish CSS
        $this->publishes([
            __DIR__.'/../resources/css' => resource_path('css/vendor/gallery-manager'),
        ], 'gallery-css');

        // Load migrations
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        // Load routes
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');

        // Register views
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'gallery-manager');
    }
}
