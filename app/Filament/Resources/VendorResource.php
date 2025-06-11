<?php

namespace App\Filament\Resources;

use App\Filament\Resources\VendorResource\Pages;
use App\Models\Vendor;
use App\Models\Location;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Filament\Resources\VendorResource\RelationManagers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Festivalbeheer';
    protected static ?string $label = 'Standen';
    protected static ?string $pluralLabel = 'Standen';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Naam')
                ->required(),
            Forms\Components\Select::make('event_id')
                ->label('Festival')
                ->relationship('event', 'name')
                ->preload()
                ->required(),
            Forms\Components\Select::make('location_id')
                ->label('Locatie op festival')
                ->relationship('location', 'name')
                ->preload()
                ->required(),
            Forms\Components\Section::make('Notities')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('Notities')
                        ->placeholder('Voeg hier notities toe over deze stand')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            Forms\Components\Section::make('Overzicht')
                ->schema([
                    Forms\Components\Repeater::make('items')
                    ->label('Artikelen overzicht')
                    ->schema([
                        Forms\Components\Placeholder::make('name')
                            ->label('Artikel')
                            ->content(fn ($record) => $record->name),
                
                        Forms\Components\Placeholder::make('unit')
                            ->label('Eenheid')
                            ->content(fn ($record) => $record->unit?->name),
                
                        Forms\Components\Placeholder::make('quantity')
                            ->label('Aantal')
                            ->content(fn ($record) => $record->quantity),
                
                        Forms\Components\Placeholder::make('total')
                            ->label('Totaal')
                            ->content(fn ($record) => $record->total),
                    ])
                    ->columns(2)
                    ->disabled(),
                ])
                ->collapsible()
                ->columnSpan([
                    'md' => 2
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),
                Tables\Columns\TextColumn::make('event.name')
                    ->label('Festival')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Locatie'),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notities'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Laatst gewijzigd')->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')->relationship('event', 'name'),
                Tables\Filters\SelectFilter::make('location')->relationship('location', 'name'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemRelationManager::class,
        ];
    }
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendors::route('/'),
            'create' => Pages\CreateVendor::route('/create'),
            'edit' => Pages\EditVendor::route('/{record}/edit'),
        ];
    }
}
