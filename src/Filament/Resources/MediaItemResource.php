<?php

namespace Mmmedia\Media\Filament\Resources;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Actions;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mmmedia\Media\Models\MediaItem;
use Mmmedia\Media\Filament\Resources\MediaItemResource\Pages;

class MediaItemResource extends Resource
{
    protected static ?string $model = MediaItem::class;

    protected static string|int|null $navigationIcon = 'heroicon-o-photo';

    protected static string|int|null $navigationGroup = 'Media';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'original_name';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('File Information')
                    ->schema([
                        Forms\Components\SpatieMediaLibraryFileUpload::make('default')
                            ->label('Upload File')
                            ->collection('default')
                            ->acceptedFileTypes(config('media.upload.allowed_mimes.image'))
                            ->maxSize(config('media.upload.max_file_size') * 1024)
                            ->image()
                            ->imageEditor()
                            ->imageEditorAspectRatios([
                                '16:9',
                                '4:3',
                                '1:1',
                            ])
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Metadata')
                    ->schema([
                        Forms\Components\TextInput::make('alt')
                            ->label('Alt Text')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\TextInput::make('title')
                            ->label('Title')
                            ->maxLength(255)
                            ->columnSpan(1),

                        Forms\Components\Textarea::make('caption')
                            ->label('Caption')
                            ->maxLength(1000)
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('thumbnail')
                    ->label('Preview')
                    ->getStateUsing(fn (MediaItem $record): ?string => $record->isImage() ? $record->url : null)
                    ->size(60)
                    ->square(),

                Tables\Columns\TextColumn::make('original_name')
                    ->label('Name')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\TextColumn::make('mime_type')
                    ->label('Type')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        str_starts_with($state, 'image/') => 'success',
                        str_starts_with($state, 'video/') => 'warning',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('formatted_size')
                    ->label('Size')
                    ->sortable(query: fn (Builder $query, string $direction): Builder => $query->orderBy('size', $direction)),

                Tables\Columns\TextColumn::make('dimensions')
                    ->label('Dimensions')
                    ->getStateUsing(fn (MediaItem $record): ?string => 
                        $record->width && $record->height ? "{$record->width} × {$record->height}" : null
                    ),

                Tables\Columns\TextColumn::make('usages_count')
                    ->label('Usages')
                    ->counts('usages')
                    ->sortable(),

                Tables\Columns\TextColumn::make('creator.name')
                    ->label('Uploaded By')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('mime_type')
                    ->label('File Type')
                    ->options([
                        'image' => 'Images',
                        'video' => 'Videos',
                        'document' => 'Documents',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'image' => $query->where('mime_type', 'like', 'image/%'),
                            'video' => $query->where('mime_type', 'like', 'video/%'),
                            'document' => $query->whereIn('mime_type', config('media.upload.allowed_mimes.document')),
                            default => $query,
                        };
                    }),

                TernaryFilter::make('has_usages')
                    ->label('Has Usages')
                    ->queries(
                        true: fn (Builder $query) => $query->has('usages'),
                        false: fn (Builder $query) => $query->doesntHave('usages'),
                    ),

                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Actions\ViewAction::make(),
                Actions\EditAction::make(),
                Actions\DeleteAction::make(),
                Actions\RestoreAction::make(),
                Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Actions\BulkActionGroup::make([
                    Actions\DeleteBulkAction::make(),
                    Actions\RestoreBulkAction::make(),
                    Actions\ForceDeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateActions([
                Actions\CreateAction::make(),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMediaItems::route('/'),
            'create' => Pages\CreateMediaItem::route('/create'),
            'view' => Pages\ViewMediaItem::route('/{record}'),
            'edit' => Pages\EditMediaItem::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                \Illuminate\Database\Eloquent\SoftDeletingScope::class,
            ]);
    }

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }
}
