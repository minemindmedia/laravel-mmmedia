<?php

namespace Mmmedia\Media\Filament\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Database\Eloquent\Model;
use Mmmedia\Media\Models\MediaItem;
use Mmmedia\Media\Support\HasMediaAttachments;

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
        
        if (!$record || !in_array(HasMediaAttachments::class, class_uses_recursive($record))) {
            return [];
        }

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

    public function getState(): mixed
    {
        $record = $this->getRecord();
        
        if (!$record || !in_array(HasMediaAttachments::class, class_uses_recursive($record))) {
            return $this->multiple ? [] : null;
        }

        $mediaItems = $record->getMedia($this->getFieldKey(), $this->getGroup());
        
        if ($this->multiple) {
            return $mediaItems->pluck('id')->toArray();
        }

        return $mediaItems->first()?->id;
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
            
            if (!$record || !in_array(HasMediaAttachments::class, class_uses_recursive($record))) {
                return;
            }

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
        });
    }
}
