<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Models\Item;
use App\Models\Category;
use App\Models\Unit;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemRelationManager extends RelationManager
{
    protected static string $relationship = 'items';
    
    protected static ?string $title = 'Producten';

    public static function getNavigationLabel(): string
    {
        return 'Custom Navigation Label';
    }

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
            ->heading('Producten')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Productnaam')
                    ->url(fn (Item $record): string => route('filament.admin.resources.items.edit', ['record' => $record]))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Categorie')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pivot.cost_price')
                    ->label('Kostprijs')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.name')
                    ->label('Eenheid')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name')
                    ->label('Categorie'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                ->modalHeading('Productkoppelen')
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Product')
                            ->options(Item::pluck('name', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')   
                                    ->required()
                                    ->label('Naam'),
                                Forms\Components\Select::make('unit_id')
                                    ->label('Eenheid')
                                    ->options(Unit::pluck('name', 'id'))
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->label('Categorie')
                                    ->options(Category::pluck('name', 'id'))
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $item = Item::create($data);
                                return $item->id;
                            }),
                        Forms\Components\TextInput::make('cost_price')
                        ->numeric()
                        ->required()
                        ->label('Kostprijs'),
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Bewerken')
                    ->modalHeading('Kostprijs bewerken')
                    ->form([
                        Forms\Components\TextInput::make('cost_price')
                            ->label('Kostprijs')
                            ->numeric()
                            ->required(),
                    ]),
                Tables\Actions\Action::make('Order')
                    ->label('Bestellen')
                    ->icon('heroicon-o-shopping-cart')
                    ->url(fn ($record) => route('filament.admin.resources.orders.create', [
                        'supplier_id' => $this->getOwnerRecord()->id,
                        'item_id' => $record->id
                    ])),
                Tables\Actions\DetachAction::make(),
            ]);
    }
}