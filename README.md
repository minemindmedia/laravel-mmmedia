# Laravel MMMedia

A comprehensive Laravel package that provides a global media library and Filament MediaPicker field for managing media assets in your Laravel applications.

## Features

- 🖼️ **Global Media Library** - Centralized media management with Filament admin interface
- 📁 **MediaPicker Field** - Easy-to-use Filament form component for media selection
- 🔗 **Usage Tracking** - Track where media items are used across your application
- 📤 **File Upload** - Direct upload support with drag & drop
- 🎨 **Multiple File Types** - Support for images, videos, and documents
- 🔄 **Reordering** - Reorder media items in galleries
- 🏷️ **Metadata** - Alt text, titles, captions, and custom metadata
- 💾 **Flexible Storage** - Works with any Laravel filesystem (local, S3, etc.)
- 🎯 **Model Integration** - Easy integration with any Eloquent model
- 🚀 **Filament v4 Compatible** - Native support for Filament v4
- 📚 **Spatie MediaLibrary Integration** - Works with or without Spatie MediaLibrary
- 🖼️ **Thumbnail Generation** - Automatic thumbnail generation with Intervention Image

## Installation

### Option 1: Install from Packagist (Recommended)

```bash
composer require minemindmedia/laravel-mmmedia
```

### Option 2: Install directly from GitHub

Add the repository to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "https://github.com/minemindmedia/laravel-mmmedia.git"
        }
    ]
}
```

Then install:

```bash
composer require minemindmedia/laravel-mmmedia:dev-main
```

Publish the configuration file:

```bash
php artisan vendor:publish --provider="Mmmedia\Media\MediaServiceProvider" --tag=media-config
```

Publish and run the migrations:

```bash
php artisan vendor:publish --provider="Mmmedia\Media\MediaServiceProvider" --tag=media-migrations
php artisan migrate
```

## Quick Start

1. **Add the trait to your model:**
```php
use Mmmedia\Media\Support\HasMediaAttachments;

class Product extends Model {
    use HasMediaAttachments;
}
```

2. **Use MediaPicker in your Filament form:**
```php
use Mmmedia\Media\Filament\Forms\Components\MediaPicker;

MediaPicker::make('featured_image_id')
    ->label('Featured Image')
    ->fieldKey('featured_image')
    ->multiple(false)
    ->allowUpload(true);
```

3. **Access the Media Library:**
Visit your Filament admin panel and you'll see a new "Media" section where you can manage all media items.

## Filament v4 Compatibility

This package is **natively compatible** with Filament v4! No vendor patches required.

### Using with Spatie MediaLibrary

If you're already using Spatie MediaLibrary, the MediaPicker will automatically detect and work with your existing media:

```php
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Product extends Model implements HasMedia
{
    use InteractsWithMedia;
    
    // MediaPicker will automatically work with your Spatie collections
}
```

### Using with Package's Media System

If you prefer to use the package's built-in media system:

```php
use Mmmedia\Media\Support\HasMediaAttachments;

class Product extends Model
{
    use HasMediaAttachments;
}
```

## Configuration

The package configuration is located in `config/media.php`. Here are the key settings:

```php
return [
    // Default disk for storing media files
    'disk' => env('MEDIA_DISK', 'public'),
    
    // Upload settings
    'upload' => [
        'max_file_size' => env('MEDIA_MAX_FILE_SIZE', 10240), // KB
        'max_files' => env('MEDIA_MAX_FILES', 10),
        'allowed_mimes' => [
            'image' => ['image/jpeg', 'image/png', 'image/gif', 'image/webp'],
            'video' => ['video/mp4', 'video/avi', 'video/mov'],
            'document' => ['application/pdf', 'application/msword'],
        ],
    ],
    
    // Storage paths
    'paths' => [
        'images' => 'media/images',
        'videos' => 'media/videos',
        'documents' => 'media/documents',
    ],
];
```

## Usage

### 1. Add the Trait to Your Models

Add the `HasMediaAttachments` trait to any model that needs media functionality:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Mmmedia\Media\Support\HasMediaAttachments;

class Product extends Model
{
    use HasMediaAttachments;
    
    // Your model code...
}
```

### 2. Use the MediaPicker in Filament Forms

```php
<?php

namespace App\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Mmmedia\Media\Filament\Forms\Components\MediaPicker;

class ProductResource extends Resource
{
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Single image picker
                MediaPicker::make('featured_image_id')
                    ->label('Featured Image')
                    ->fieldKey('featured_image')
                    ->multiple(false)
                    ->allowUpload(true),
                
                // Multiple image gallery
                MediaPicker::make('gallery_ids')
                    ->label('Gallery')
                    ->fieldKey('gallery')
                    ->multiple(true)
                    ->maxFiles(10)
                    ->allowUpload(true)
                    ->reorderable(true),
                
                // Document picker
                MediaPicker::make('document_id')
                    ->label('Document')
                    ->fieldKey('document')
                    ->multiple(false)
                    ->allowedMimes(['application/pdf', 'application/msword'])
                    ->allowUpload(true),
            ]);
    }
}
```

### 3. Working with Media in Your Code

```php
use App\Models\Product;
use Mmmedia\Media\Models\MediaItem;

$product = Product::find(1);

// Get media items
$featuredImage = $product->getFirstMedia('featured_image');
$gallery = $product->getMedia('gallery');

// Attach media
$mediaItem = MediaItem::find('some-id');
$product->attachMedia('featured_image', $mediaItem);

// Sync media (replace existing)
$product->syncMedia('gallery', ['media-id-1', 'media-id-2']);

// Check if model has media
if ($product->hasMedia('featured_image')) {
    // Do something
}

// Get media count
$count = $product->getMediaCount('gallery');

// Detach media
$product->detachMedia('featured_image');
```

### 4. MediaPicker Configuration Options

The MediaPicker component supports various configuration options:

```php
MediaPicker::make('field_name')
    ->multiple(true)                    // Allow multiple selection
    ->allowUpload(true)                 // Enable direct upload
    ->maxFiles(5)                       // Maximum number of files
    ->allowedMimes(['image/jpeg'])      // Restrict file types
    ->fieldKey('custom_key')            // Custom field key for usage tracking
    ->group('gallery')                  // Group media items
    ->reorderable(true);                // Enable reordering
```

## Media Library Admin

The package automatically registers a `MediaItemResource` in your Filament admin panel. You can:

- Browse all media items in a grid or list view
- Upload new media files
- Edit metadata (alt text, title, caption)
- View usage information
- Delete unused media items
- Filter by file type, usage status, etc.

## API Endpoints

The package provides API endpoints for programmatic access:

```php
// Upload a file
POST /api/media/upload
Content-Type: multipart/form-data

// Update media metadata
PUT /api/media/{mediaItem}
{
    "alt": "New alt text",
    "title": "New title",
    "caption": "New caption"
}

// Delete media item
DELETE /api/media/{mediaItem}
```

## Advanced Usage

### Custom Storage Paths

You can customize where different file types are stored:

```php
// In config/media.php
'paths' => [
    'images' => 'uploads/images',
    'videos' => 'uploads/videos',
    'documents' => 'uploads/documents',
],
```

### Thumbnail Generation

The package supports thumbnail generation for images:

```php
// In config/media.php
'thumbnails' => [
    'enabled' => true,
    'sizes' => [
        'small' => [150, 150],
        'medium' => [300, 300],
        'large' => [600, 600],
    ],
    'quality' => 80,
],
```

### Usage Tracking

The package automatically tracks where media items are used:

```php
// Get all usages of a media item
$mediaItem = MediaItem::find('some-id');
$usages = $mediaItem->usages;

foreach ($usages as $usage) {
    echo "Used in: {$usage->model_type} #{$usage->model_id}";
    echo "Field: {$usage->field_key}";
    echo "Position: {$usage->position}";
}
```

## Events

The package fires events when media items are created, updated, or deleted:

```php
use Mmmedia\Media\Events\MediaItemCreated;
use Mmmedia\Media\Events\MediaItemUpdated;
use Mmmedia\Media\Events\MediaItemDeleted;

// Listen for events
Event::listen(MediaItemCreated::class, function ($mediaItem) {
    // Generate thumbnails, etc.
});
```

## Thumbnail Generation

The package supports automatic thumbnail generation using Intervention Image:

### Install Intervention Image (Optional)

```bash
composer require intervention/image
```

### Generate Thumbnails

```bash
# Generate thumbnails for all media items
php artisan media:generate-thumbnails

# Force regeneration of existing thumbnails
php artisan media:generate-thumbnails --force
```

### Custom Thumbnail Configuration

You can configure thumbnail generation in `config/media.php`:

```php
'thumbnails' => [
    'enabled' => true,
    'sizes' => [
        'small' => [150, 150],
        'medium' => [300, 300],
        'large' => [600, 600],
    ],
    'quality' => 80,
],
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email security@minemindmedia.com instead of using the issue tracker.

## Credits

- [MineMind Media](https://github.com/minemindmedia)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

## Support

For support, please email support@minemindmedia.com or create an issue on GitHub.
# Auto-update test

<!-- Auto-update test -->
