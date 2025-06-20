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
                    ->label('Leverancier')
                    ->url(fn (Supplier $record): string => route('filament.admin.resources.suppliers.edit', ['record' => $record])),
                Tables\Columns\TextColumn::make('contact_email')
                    ->label('E-mail'),
                Tables\Columns\TextColumn::make('contact_phone')
                    ->label('Telefoon'),
                Tables\Columns\TextColumn::make('pivot.cost_price')->label('Cost Price')
                    ->label('Kostprijs'),
            ])
            ->headerActions([
                AttachAction::make()
                ->modalHeading('Leverancier koppelen')
                        ->form([
                            Forms\Components\Select::make('recordId')
                                ->required()
                                ->label('Leverancier')
                                ->options(Supplier::pluck('name', 'id'))
                                ->searchable()
                                ->createOptionForm([
                                    Forms\Components\TextInput::make('name')
                                        ->required()
                                        ->label('Naam leverancier'),
                                    Forms\Components\TextInput::make('contact_email')
                                        ->required()
                                        ->email()
                                        ->label('E-mail'),
                                    Forms\Components\TextInput::make('contact_phone')
                                        ->required()
                                        ->label('Telefoon'),
                                ])
                                ->createOptionUsing(function (array $data): int {
                                    $supplier = Supplier::create($data);
                                    return $supplier->id;
                                }),
                            Forms\Components\TextInput::make('cost_price')
                                ->numeric()
                                ->label('Kostprijs')
                                ->required(),
                        ]),
            ])
            ->actions([
                EditAction::make()
                ->modalHeading('Kostprijs bewerken')
                    ->form([
                        Forms\Components\TextInput::make('cost_price')
                            ->numeric()
                            ->label('Kostprijs')
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
                    ->requiresConfirmation(),
            ]);
    }
}
