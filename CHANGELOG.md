# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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
