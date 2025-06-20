<?php

namespace App\Filament\Resources\ItemResource\RelationManagers;

use App\Models\Supplier;
use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\AttachAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\DetachAction;

class SupplierRelationManager extends RelationManager
{
    protected static string $relationship = 'suppliers';

    protected static ?string $title = 'Leveranciers';

    protected static ?string $pluralLabel = 'Leveranciers';

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Supplier')
                    ->url(fn (Supplier $record): ?string => auth()->user()->hasRole('admin') ? route('filament.admin.resources.suppliers.edit', ['record' => $record]) : null),
                Tables\Columns\TextColumn::make('contact_email'),
                Tables\Columns\TextColumn::make('contact_phone'),
                Tables\Columns\TextColumn::make('pivot.cost_price')->label('Cost Price'),
            ])
            ->headerActions([
                AttachAction::make()
                        ->form([
                            Forms\Components\Select::make('recordId')
                                ->label('Supplier')
                                ->options(Supplier::pluck('name', 'id'))
                                ->searchable()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')->required(),
                                    Forms\Components\TextInput::make('contact_email')->required()->email(),
                                    Forms\Components\TextInput::make('contact_phone')->required(),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $supplier = Supplier::create($data);
                                    return $supplier->id;
                                }),
                            Forms\Components\TextInput::make('cost_price')->numeric()->required(),
                        ])
                        ->visible(auth()->user()->hasRole('admin')),
            ])
            ->actions([
                EditAction::make()
                    ->label('')
                    ->form([
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->required(),
                    ]),
                Tables\Actions\Action::make('order')
                    ->label('Bestellen')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn ($record) => route('filament.admin.resources.orders.create', [
                        'supplier_id' => $record->id,
                        'item_id' => $this->getOwnerRecord()->id 
                    ])),
                DetachAction::make()
                    ->requiresConfirmation()
                    ->visible(auth()->user()->hasRole('admin')),
            ]);
    }
}
