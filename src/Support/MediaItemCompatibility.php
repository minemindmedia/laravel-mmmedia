<?php

namespace Mmmedia\Media\Support;

use Illuminate\Support\Facades\Storage;

/**
 * Compatibility helper for custom MediaItem implementations
 * 
 * This trait provides methods that can be used in custom MediaItem models
 * to ensure compatibility with the package while allowing customization.
 */
trait MediaItemCompatibility
{
    /**
     * Get thumbnail URL with fallback
     * Override this method in your custom MediaItem model
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->isImage()) {
            return null;
        }

        // Try to get Spatie conversion first
        if (method_exists($this, 'hasMedia') && $this->hasMedia('default')) {
            $media = $this->getFirstMedia('default');
            if ($media && $media->hasGeneratedConversion('thumb')) {
                return $media->getUrl('thumb');
            }
        }

        // Fallback to original URL
        return $this->url ?? null;
    }

    /**
     * Get thumbnail attribute (alias for compatibility)
     * Override this method in your custom MediaItem model
     */
    public function getThumbnailAttribute(): ?string
    {
        return $this->getThumbnailUrlAttribute();
    }

    /**
     * Check if this is an image
     */
    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    /**
     * Check if this is a video
     */
    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

    /**
     * Check if this is a document
     */
    public function isDocument(): bool
    {
        return in_array($this->mime_type, [
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Get formatted file size
     */
    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size ?? 0;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Get dimensions as string
     */
    public function getDimensionsAttribute(): ?string
    {
        if ($this->width && $this->height) {
            return "{$this->width} × {$this->height}";
        }
        return null;
    }

    /**
     * Generate thumbnail using Intervention Image (if available)
     * This method can be overridden in custom implementations
     */
    public function generateThumbnail(): ?string
    {
        if (!$this->isImage() || !class_exists(\Intervention\Image\ImageManager::class)) {
            return null;
        }

        try {
            $manager = new \Intervention\Image\ImageManager(new \Intervention\Image\Drivers\Gd\Driver());
            $image = $manager->read(Storage::disk($this->disk)->path($this->path));
            
            $thumbnailPath = 'thumbnails/' . pathinfo($this->path, PATHINFO_FILENAME) . '_thumb.jpg';
            
            $image->resize(300, 300, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })->save(Storage::disk('public')->path($thumbnailPath), 90);

            return Storage::disk('public')->url($thumbnailPath);
        } catch (\Exception $e) {
            return null;
        }
    }
}
