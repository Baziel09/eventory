<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use Filament\Forms;
use Filament\Tables;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Resources\RelationManagers\RelationManager;

class OrderRelationManager extends RelationManager
{
    protected static string $relationship = 'orders'; // this must match the method name in Supplier model

    public function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\DatePicker::make('ordered_at')
                ->required()
                ->label('Order Date'),

            Forms\Components\Select::make('status')
                ->options([
                    'pending' => 'Pending',
                    'shipped' => 'Shipped',
                    'delivered' => 'Delivered',
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            Tables\Columns\TextColumn::make('id'),
            Tables\Columns\TextColumn::make('ordered_at')->date(),
            Tables\Columns\TextColumn::make('status')->badge(),
        ])
        ->headerActions([
            Tables\Actions\CreateAction::make(),
        ])
        ->actions([
            Tables\Actions\EditAction::make(),
            Tables\Actions\DeleteAction::make(),
        ]);
    }
}
