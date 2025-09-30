# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.3] - 2024-01-XX

### Fixed
- Fixed final Tab class import in ListMediaItems for complete Filament v4 compatibility
- Updated to use `Filament\Schemas\Components\Tabs\Tab` for native Filament v4 support

### Achievement
- **Complete native Filament v4 compatibility achieved!**
- **No vendor patches required!**

## [1.1.2] - 2024-01-XX

### Fixed
- Fixed Tab class import in ListMediaItems for Filament v4 compatibility
- Made generateThumbnail method public to avoid visibility conflicts
- Added generateThumbnail method to MediaItemCompatibility trait
- Improved method override support for custom implementations

### Added
- Complete thumbnail generation support in compatibility trait
- Better method visibility for custom model overrides

## [1.1.1] - 2024-01-XX

### Fixed
- Fixed navigation type declarations for Filament v4 (`string|int|null`)
- Resolved method conflicts with custom MediaItem implementations
- Updated form structure to use SpatieMediaLibraryFileUpload
- Added MediaItemCompatibility trait for custom implementations
- Added configuration option to disable automatic model binding
- Improved compatibility with existing custom MediaItem models

### Added
- MediaItemCompatibility trait for easy custom model creation
- Configuration option `allow_custom_model` to control model binding
- Better documentation for custom MediaItem implementations

## [1.1.0] - 2024-01-XX

### Added
- Native Filament v4 compatibility (no vendor patches required)
- Spatie MediaLibrary integration support
- Automatic thumbnail generation with Intervention Image
- Console command for generating thumbnails (`media:generate-thumbnails`)
- Enhanced MediaPicker with dual system support
- Custom model binding for MediaItem
- Improved thumbnail handling with fallbacks

### Fixed
- Updated version constraints to support Laravel 12
- Added support for Filament 4.x
- Added support for Spatie MediaLibrary 11.x
- Added support for Livewire 4.x
- Fixed action namespaces for Filament v4
- Improved MediaPicker state handling for both systems

## [1.0.1] - 2024-01-XX

### Fixed
- Updated version constraints to support Laravel 12
- Added support for Filament 4.x
- Added support for Spatie Media Library 11.x
- Added support for Livewire 4.x

## [1.0.0] - 2024-01-XX

### Added
- Initial release of Laravel MMMedia package
- Global Media Library with Filament admin interface
- MediaPicker Filament form component
- Usage tracking system for media items
- Support for images, videos, and documents
- Drag & drop file upload
- Media reordering functionality
- Metadata management (alt text, title, caption)
- Flexible storage configuration
- HasMediaAttachments trait for models
- API endpoints for media management
- Thumbnail generation support
- Comprehensive configuration options

### Features
- **MediaItem Model**: Core model for storing media metadata
- **MediaUsage Model**: Tracks where media items are used
- **MediaItemResource**: Filament admin interface for media management
- **MediaPicker Component**: Form field for media selection
- **UploadController**: Handles file uploads and management
- **HasMediaAttachments Trait**: Easy model integration
- **Configurable Storage**: Works with any Laravel filesystem
- **Usage Tracking**: Know where each media item is used
- **File Type Support**: Images, videos, documents
- **Reordering**: Drag & drop reordering for galleries
- **Metadata**: Alt text, titles, captions support
- **Thumbnails**: Automatic thumbnail generation
- **API Endpoints**: RESTful API for media operations
