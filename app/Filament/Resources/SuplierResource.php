<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SuplierResource\Pages;
use App\Filament\Resources\SuplierResource\RelationManagers;
use App\Models\Suplier;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SuplierResource extends Resource
{
    protected static ?string $model = Suplier::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListSupliers::route('/'),
            'create' => Pages\CreateSuplier::route('/create'),
            'edit' => Pages\EditSuplier::route('/{record}/edit'),
        ];
    }
}
