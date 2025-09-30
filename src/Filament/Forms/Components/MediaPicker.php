<?php

namespace Mmmedia\Media\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Mmmedia\Media\Models\MediaItem;
use Mmmedia\Media\Support\HasMediaAttachments;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class MediaPicker extends Field
{
    protected string $view = 'media::forms.components.media-picker';

    protected bool $multiple = false;

    protected bool $allowUpload = true;

    protected int $maxFiles = 10;

    protected array $allowedMimes = [];

    protected ?string $fieldKey = null;

    protected ?string $group = null;

    protected bool $reorderable = true;

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    public function allowUpload(bool $allowUpload = true): static
    {
        $this->allowUpload = $allowUpload;

        return $this;
    }

    public function maxFiles(int $maxFiles): static
    {
        $this->maxFiles = $maxFiles;

        return $this;
    }

    public function allowedMimes(array $mimes): static
    {
        $this->allowedMimes = $mimes;

        return $this;
    }

    public function fieldKey(string $fieldKey): static
    {
        $this->fieldKey = $fieldKey;

        return $this;
    }

    public function group(?string $group): static
    {
        $this->group = $group;

        return $this;
    }

    public function reorderable(bool $reorderable = true): static
    {
        $this->reorderable = $reorderable;

        return $this;
    }

    public function getMultiple(): bool
    {
        return $this->multiple;
    }

    public function getAllowUpload(): bool
    {
        return $this->allowUpload;
    }

    public function getMaxFiles(): int
    {
        return $this->maxFiles;
    }

    public function getAllowedMimes(): array
    {
        return $this->allowedMimes ?: config('media.upload.allowed_mimes.image');
    }

    public function getFieldKey(): ?string
    {
        return $this->fieldKey ?: $this->getName();
    }

    public function getGroup(): ?string
    {
        return $this->group;
    }

    public function getReorderable(): bool
    {
        return $this->reorderable;
    }

    public function getMediaItems(): array
    {
        $record = $this->getRecord();
        
        if (!$record) {
            return [];
        }

        // Check if using Spatie MediaLibrary
        if (in_array(InteractsWithMedia::class, class_uses_recursive($record))) {
            return $this->getSpatieMediaItems($record);
        }

        // Check if using HasMediaAttachments trait
        if (in_array(HasMediaAttachments::class, class_uses_recursive($record))) {
            return $this->getPackageMediaItems($record);
        }

        return [];
    }

    protected function getSpatieMediaItems($record): array
    {
        $collection = $this->getFieldKey();
        $mediaItems = $record->getMedia($collection);
        
        return $mediaItems->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $media->getUrl(),
                'thumbnail_url' => $media->getUrl('thumb') ?: $media->getUrl(),
                'original_name' => $media->name,
                'mime_type' => $media->mime_type,
                'size' => $this->formatBytes($media->size),
                'width' => $media->getCustomProperty('width'),
                'height' => $media->getCustomProperty('height'),
                'alt' => $media->getCustomProperty('alt'),
                'title' => $media->getCustomProperty('title'),
                'caption' => $media->getCustomProperty('caption'),
                'is_image' => str_starts_with($media->mime_type, 'image/'),
                'is_video' => str_starts_with($media->mime_type, 'video/'),
                'is_document' => in_array($media->mime_type, [
                    'application/pdf',
                    'application/msword',
                    'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                ]),
            ];
        })->toArray();
    }

    protected function getPackageMediaItems($record): array
    {
        $mediaItems = $record->getMedia($this->getFieldKey(), $this->getGroup());
        
        return $mediaItems->map(function (MediaItem $item) {
            return [
                'id' => $item->id,
                'url' => $item->url,
                'thumbnail_url' => $item->thumbnail_url,
                'original_name' => $item->original_name,
                'mime_type' => $item->mime_type,
                'size' => $item->formatted_size,
                'width' => $item->width,
                'height' => $item->height,
                'alt' => $item->alt,
                'title' => $item->title,
                'caption' => $item->caption,
                'is_image' => $item->isImage(),
                'is_video' => $item->isVideo(),
                'is_document' => $item->isDocument(),
            ];
        })->toArray();
    }

    protected function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getState(): mixed
    {
        $record = $this->getRecord();
        
        if (!$record) {
            return $this->multiple ? [] : null;
        }

        // Check if using Spatie MediaLibrary
        if (in_array(InteractsWithMedia::class, class_uses_recursive($record))) {
            $mediaItems = $record->getMedia($this->getFieldKey());
            
            if ($this->multiple) {
                return $mediaItems->pluck('id')->toArray();
            }

            return $mediaItems->first()?->id;
        }

        // Check if using HasMediaAttachments trait
        if (in_array(HasMediaAttachments::class, class_uses_recursive($record))) {
            $mediaItems = $record->getMedia($this->getFieldKey(), $this->getGroup());
            
            if ($this->multiple) {
                return $mediaItems->pluck('id')->toArray();
            }

            return $mediaItems->first()?->id;
        }

        return $this->multiple ? [] : null;
    }

    public function hydrateDefaultState(): void
    {
        if ($this->getState() !== null) {
            return;
        }

        $this->state($this->getState());
    }

    public function afterStateUpdated(?Closure $callback): static
    {
        $this->afterStateUpdated = $callback;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->afterStateHydrated(function (MediaPicker $component, $state): void {
            if ($state !== null) {
                return;
            }

            $component->state($component->getState());
        });

        $this->afterStateUpdated(function (MediaPicker $component, $state): void {
            $record = $component->getRecord();
            
            if (!$record) {
                return;
            }

            // Handle Spatie MediaLibrary
            if (in_array(InteractsWithMedia::class, class_uses_recursive($record))) {
                $collection = $component->getFieldKey();
                
                if ($component->getMultiple()) {
                    $mediaIds = is_array($state) ? $state : [];
                    $record->clearMediaCollection($collection);
                    
                    foreach ($mediaIds as $mediaId) {
                        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                        if ($media) {
                            $media->move($record, $collection);
                        }
                    }
                } else {
                    $mediaId = $state;
                    $record->clearMediaCollection($collection);
                    
                    if ($mediaId) {
                        $media = \Spatie\MediaLibrary\MediaCollections\Models\Media::find($mediaId);
                        if ($media) {
                            $media->move($record, $collection);
                        }
                    }
                }
                return;
            }

            // Handle HasMediaAttachments trait
            if (in_array(HasMediaAttachments::class, class_uses_recursive($record))) {
                if ($component->getMultiple()) {
                    $mediaItemIds = is_array($state) ? $state : [];
                    $record->syncMedia($component->getFieldKey(), $mediaItemIds, $component->getGroup());
                } else {
                    $mediaItemId = $state;
                    if ($mediaItemId) {
                        $record->syncMedia($component->getFieldKey(), [$mediaItemId], $component->getGroup());
                    } else {
                        $record->detachMedia($component->getFieldKey(), $component->getGroup());
                    }
                }
            }
        });
    }
}
