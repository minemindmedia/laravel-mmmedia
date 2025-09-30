<?php

namespace Mmmedia\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class MediaItem extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasUlids, InteractsWithMedia;

    protected $fillable = [
        'disk',
        'path',
        'original_name',
        'mime_type',
        'size',
        'width',
        'height',
        'alt',
        'title',
        'caption',
        'meta',
        'created_by',
    ];

    protected $casts = [
        'meta' => 'array',
        'size' => 'integer',
        'width' => 'integer',
        'height' => 'integer',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(config('auth.providers.users.model'), 'created_by');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class);
    }

    public function getUrlAttribute(): string
    {
        return Storage::disk($this->disk)->url($this->path);
    }

    public function getFullPathAttribute(): string
    {
        return Storage::disk($this->disk)->path($this->path);
    }

    public function isImage(): bool
    {
        return str_starts_with($this->mime_type, 'image/');
    }

    public function isVideo(): bool
    {
        return str_starts_with($this->mime_type, 'video/');
    }

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

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->isImage()) {
            return null;
        }

        // For now, return the original URL
        // In a real implementation, you might want to generate thumbnails
        return $this->url;
    }

    public function delete(): bool
    {
        // Delete the actual file
        if (Storage::disk($this->disk)->exists($this->path)) {
            Storage::disk($this->disk)->delete($this->path);
        }

        return parent::delete();
    }

    /**
     * Register media conversions for Spatie MediaLibrary
     */
    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10)
            ->quality(90)
            ->nonQueued();

        $this->addMediaConversion('grid')
            ->width(500)
            ->height(500)
            ->sharpen(10)
            ->quality(90)
            ->nonQueued();
    }

    /**
     * Register media collections for Spatie MediaLibrary
     */
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('default')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/webp']);
    }

    /**
     * Generate thumbnail using Intervention Image (if available)
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

    /**
     * Get thumbnail URL with fallback
     */
    public function getThumbnailUrlAttribute(): ?string
    {
        if (!$this->isImage()) {
            return null;
        }

        // Try to get Spatie conversion first
        if ($this->hasMedia('default')) {
            $media = $this->getFirstMedia('default');
            if ($media && $media->hasGeneratedConversion('thumb')) {
                return $media->getUrl('thumb');
            }
        }

        // Fallback to generated thumbnail
        return $this->generateThumbnail() ?: $this->url;
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
}
