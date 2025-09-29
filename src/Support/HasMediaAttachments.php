<?php

namespace Mmmedia\Media\Support;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Collection;
use Mmmedia\Media\Models\MediaItem;
use Mmmedia\Media\Models\MediaUsage;

trait HasMediaAttachments
{
    public function mediaUsages(): HasMany
    {
        return $this->hasMany(MediaUsage::class, 'model_id')
            ->where('model_type', static::class);
    }

    public function mediaItems(): HasManyThrough
    {
        return $this->hasManyThrough(
            MediaItem::class,
            MediaUsage::class,
            'model_id',
            'id',
            'id',
            'media_item_id'
        )->where('media_usages.model_type', static::class);
    }

    public function getMedia(string $fieldKey, ?string $group = null): Collection
    {
        $query = $this->mediaUsages()
            ->where('field_key', $fieldKey)
            ->with('mediaItem');

        if ($group) {
            $query->where('group', $group);
        }

        return $query->orderBy('position')
            ->get()
            ->pluck('mediaItem')
            ->filter();
    }

    public function getFirstMedia(string $fieldKey, ?string $group = null): ?MediaItem
    {
        return $this->getMedia($fieldKey, $group)->first();
    }

    public function attachMedia(string $fieldKey, MediaItem|array $items, ?string $group = null, ?int $position = null): void
    {
        $items = is_array($items) ? $items : [$items];

        foreach ($items as $index => $item) {
            if ($item instanceof MediaItem) {
                $this->mediaUsages()->create([
                    'media_item_id' => $item->id,
                    'field_key' => $fieldKey,
                    'group' => $group,
                    'position' => $position ?? $index,
                ]);
            }
        }
    }

    public function syncMedia(string $fieldKey, array $mediaItemIds, ?string $group = null): void
    {
        // Remove existing usages for this field
        $this->mediaUsages()
            ->where('field_key', $fieldKey)
            ->when($group, fn($query) => $query->where('group', $group))
            ->delete();

        // Add new usages
        foreach ($mediaItemIds as $index => $mediaItemId) {
            if ($mediaItemId) {
                $this->mediaUsages()->create([
                    'media_item_id' => $mediaItemId,
                    'field_key' => $fieldKey,
                    'group' => $group,
                    'position' => $index,
                ]);
            }
        }
    }

    public function detachMedia(string $fieldKey, ?string $group = null): void
    {
        $this->mediaUsages()
            ->where('field_key', $fieldKey)
            ->when($group, fn($query) => $query->where('group', $group))
            ->delete();
    }

    public function detachMediaItem(string $fieldKey, string $mediaItemId, ?string $group = null): void
    {
        $this->mediaUsages()
            ->where('field_key', $fieldKey)
            ->where('media_item_id', $mediaItemId)
            ->when($group, fn($query) => $query->where('group', $group))
            ->delete();
    }

    public function hasMedia(string $fieldKey, ?string $group = null): bool
    {
        return $this->mediaUsages()
            ->where('field_key', $fieldKey)
            ->when($group, fn($query) => $query->where('group', $group))
            ->exists();
    }

    public function getMediaCount(string $fieldKey, ?string $group = null): int
    {
        return $this->mediaUsages()
            ->where('field_key', $fieldKey)
            ->when($group, fn($query) => $query->where('group', $group))
            ->count();
    }

    public function getAllMedia(): Collection
    {
        return $this->mediaUsages()
            ->with('mediaItem')
            ->get()
            ->pluck('mediaItem')
            ->filter();
    }

    public function getMediaByGroup(?string $group = null): Collection
    {
        return $this->mediaUsages()
            ->when($group, fn($query) => $query->where('group', $group))
            ->with('mediaItem')
            ->get()
            ->groupBy('field_key')
            ->map(fn($usages) => $usages->pluck('mediaItem')->filter());
    }
}
