<?php

namespace Mmmedia\Media\Filament\Resources\MediaItemResource\Pages;

use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Mmmedia\Media\Filament\Resources\MediaItemResource;

class EditMediaItem extends EditRecord
{
    protected static string $resource = MediaItemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
