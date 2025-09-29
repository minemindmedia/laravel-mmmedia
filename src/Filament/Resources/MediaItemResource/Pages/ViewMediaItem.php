<?php

namespace Mmmedia\Media\Filament\Resources\MediaItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Mmmedia\Media\Filament\Resources\MediaItemResource;

class ViewMediaItem extends ViewRecord
{
    protected static string $resource = MediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
