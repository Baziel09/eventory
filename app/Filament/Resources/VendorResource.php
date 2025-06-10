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
                ->label('Naam van voorraadbeheerder')
                ->required(),
            Forms\Components\TextInput::make('email')
                ->email()
                ->maxLength(255),
            Forms\Components\TextInput::make('phone')
                ->tel()
                ->maxLength(255),   
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
                
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Naam')->searchable(),
                Tables\Columns\TextColumn::make('email')->label('E-mail')->searchable(),
                Tables\Columns\TextColumn::make('phone')->label('Telefoon'),
                Tables\Columns\TextColumn::make('event.name')->label('Festival'),
                Tables\Columns\TextColumn::make('location.name')->label('Locatie'),
                Tables\Columns\TextColumn::make('created_at')->label('Aangemaakt')->dateTime(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ]);
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