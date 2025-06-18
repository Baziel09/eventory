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
use Filament\Navigation\NavigationItem;
use Filament\Facades\Filament;


class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Festivalbeheer';
    protected static ?string $label = 'Standen';
    protected static ?string $pluralLabel = 'Standen';
    

    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::count();
    }

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
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('location.name')
                    ->label('Locatie')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes')
                    ->label('Notities')
                    ->limit(25)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Laatst gewijzigd')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('event')
                    ->relationship('event', 'name')
                    ->label('Festival'),
                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->label('Locatie op festival'),

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
            // RelationManagers\StocksRelationManager::class,
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
