<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Disk
    |--------------------------------------------------------------------------
    |
    | The default disk to use for storing media files.
    |
    */
    'disk' => env('MEDIA_DISK', 'public'),

    /*
    |--------------------------------------------------------------------------
    | Upload Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for file uploads including size limits and allowed types.
    |
    */
    'upload' => [
        'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10240), // KB
        'max_files' => env('MEDIA_MAX_FILES', 10),
        'allowed_mimes' => [
            'image' => [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'image/svg+xml',
            ],
            'video' => [
                'video/mp4',
                'video/avi',
                'video/mov',
                'video/wmv',
                'video/webm',
            ],
            'document' => [
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'text/plain',
                'text/csv',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Thumbnail Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for generating thumbnails of images.
    |
    */
    'thumbnails' => [
        'enabled' => env('MEDIA_THUMBNAILS_ENABLED', true),
        'sizes' => [
            'small' => [150, 150],
            'medium' => [300, 300],
            'large' => [600, 600],
        ],
        'quality' => env('MEDIA_THUMBNAIL_QUALITY', 80),
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage Paths
    |--------------------------------------------------------------------------
    |
    | Paths for storing different types of media files.
    |
    */
    'paths' => [
        'images' => 'media/images',
        'videos' => 'media/videos',
        'documents' => 'media/documents',
        'thumbnails' => 'media/thumbnails',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filament Resource Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the Filament MediaItemResource.
    |
    */
    'filament' => [
        'resource' => [
            'navigation_group' => 'Media',
            'navigation_sort' => 1,
            'navigation_icon' => 'heroicon-o-photo',
        ],
        'index' => [
            'default_view' => 'grid', // 'grid' or 'list'
            'per_page' => 24,
            'grid_columns' => 6,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Media Picker Settings
    |--------------------------------------------------------------------------
    |
    | Default settings for the MediaPicker component.
    |
    */
    'picker' => [
        'default_multiple' => false,
        'default_allow_upload' => true,
        'default_max_files' => 10,
        'modal_width' => '7xl',
        'grid_columns' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for caching media metadata and thumbnails.
    |
    */
    'cache' => [
        'enabled' => env('MEDIA_CACHE_ENABLED', true),
        'ttl' => env('MEDIA_CACHE_TTL', 3600), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Model Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for model binding and customization.
    |
    */
    'allow_custom_model' => env('MEDIA_ALLOW_CUSTOM_MODEL', true),
];
