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
use Mmmedia\Media\Support\MediaItemCompatibility;

class MediaItem extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, HasUlids, InteractsWithMedia, MediaItemCompatibility;

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

}
