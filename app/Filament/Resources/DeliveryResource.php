<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DeliveryResource\Pages;
use App\Filament\Resources\DeliveryResource\RelationManagers;
use App\Models\Delivery;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DeliveryResource extends Resource
{
    protected static ?string $model = Delivery::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $activeNavigationIcon = 'heroicon-s-truck';   

    protected static ?string $navigationGroup = 'Inkoop & Leveringen';

    protected static ?string $navigationLabel = 'Leveringen';
    
    protected static ?string $pluralLabel = 'Leveringen';
    
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('order_id')
                    ->label('Bestelnummer')
                    ->relationship('order', 'id')
                    ->required(),
                Forms\Components\DateTimePicker::make('delivered_at'),
                Forms\Components\select::make('user_id')
                    ->label('Geaccepteerd door:')
                    ->relationship('user', 'name')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order.id')
                    ->numeric()
                    ->sortable()
                    ->label('Bestelnummer'),
                Tables\Columns\TextColumn::make('delivered_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Levering geaccepteerd op'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Geaccepteerd door')
                    //->url(fn (User $record): string => route('filament.admin.resources.users.edit', ['record' => $record]))
                    ->searchable()
                    ->label('Geaccepteerd door')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Aangepast op')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->label('Verwijderd op')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\Action::make('ViewOrder')
                    ->label('Bestelling')
                    ->color('warning')
                    ->icon('heroicon-o-arrow-top-right-on-square')
                    ->url(fn (Delivery $record): string => route('filament.admin.resources.orders.edit', ['record' => $record->order_id])),
                Tables\Actions\EditAction::make()
                    ->label(''),  
                Tables\Actions\DeleteAction::make()
                    ->label(''),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
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
            'index' => Pages\ListDeliveries::route('/'),
            'create' => Pages\CreateDelivery::route('/create'),
            'edit' => Pages\EditDelivery::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
