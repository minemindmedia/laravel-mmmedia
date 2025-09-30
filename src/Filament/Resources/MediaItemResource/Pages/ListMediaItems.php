<?php

namespace Mmmedia\Media\Filament\Resources\MediaItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;
use Mmmedia\Media\Filament\Resources\MediaItemResource;

class ListMediaItems extends ListRecords
{
    protected static string $resource = MediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All'),
            'images' => Tab::make('Images')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'like', 'image/%'))
                ->badge(fn () => MediaItemResource::getModel()::where('mime_type', 'like', 'image/%')->count()),
            'videos' => Tab::make('Videos')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('mime_type', 'like', 'video/%'))
                ->badge(fn () => MediaItemResource::getModel()::where('mime_type', 'like', 'video/%')->count()),
            'documents' => Tab::make('Documents')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereIn('mime_type', config('media.upload.allowed_mimes.document')))
                ->badge(fn () => MediaItemResource::getModel()::whereIn('mime_type', config('media.upload.allowed_mimes.document'))->count()),
        ];
    }
}