<?php

namespace Mmmedia\Media\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MediaUsage extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'media_item_id',
        'model_type',
        'model_id',
        'field_key',
        'group',
        'position',
    ];

    protected $casts = [
        'position' => 'integer',
    ];

    public function mediaItem(): BelongsTo
    {
        return $this->belongsTo(MediaItem::class);
    }

    public function model(): MorphTo
    {
        return $this->morphTo('model', 'model_type', 'model_id');
    }

    public function getModelAttribute()
    {
        return $this->model();
    }
}
