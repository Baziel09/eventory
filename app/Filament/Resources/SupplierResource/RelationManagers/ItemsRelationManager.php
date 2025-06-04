<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Models\Item;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('cost_price')
                    ->numeric()
                    ->required()
                    ->label('Cost Price'),
            ]);
    }

        public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Item Name')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('pivot.cost_price')->label('Cost Price'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->form([
                    Forms\Components\Select::make('recordId')
                        ->label('Item')
                        ->options(Item::pluck('name', 'id'))
                        ->searchable()
                        ->createOptionForm([
                            Forms\Components\TextInput::make('name')->required(),
                            Forms\Components\TextInput::make('unit')->required(),
                            Forms\Components\Select::make('category_id')
                                ->relationship('category', 'name')
                                ->required(),
                        ]),
                    Forms\Components\TextInput::make('cost_price')->numeric()->required(),
                ]),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Edit')
                    ->form([
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Cost Price')
                            ->numeric()
                            ->required(),
                    ]),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}
