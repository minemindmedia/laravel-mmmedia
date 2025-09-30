<?php

namespace Mmmedia\Media;

use Illuminate\Support\ServiceProvider;
use Mmmedia\Media\Models\MediaItem;
use Mmmedia\Media\Models\MediaUsage;
use Mmmedia\Media\Console\Commands\GenerateMediaThumbnails;

class MediaServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        
        $this->publishes([
            __DIR__ . '/../config/media.php' => config_path('media.php'),
        ], 'media-config');

        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'media-migrations');

        // Register Filament resources
        if (class_exists(\Filament\Filament::class)) {
            \Filament\Filament::serving(function () {
                \Filament\Filament::registerResources([
                    \Mmmedia\Media\Filament\Resources\MediaItemResource::class,
                ]);
            });
        }

        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                GenerateMediaThumbnails::class,
            ]);
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/media.php',
            'media'
        );

        // Bind models
        $this->app->bind(MediaItem::class);
        $this->app->bind(MediaUsage::class);

        // Allow custom MediaItem model binding (only if enabled in config)
        if (config('media.allow_custom_model', true) && class_exists(\App\Models\MediaItem::class)) {
            $this->app->bind(MediaItem::class, \App\Models\MediaItem::class);
        }
    }
}
