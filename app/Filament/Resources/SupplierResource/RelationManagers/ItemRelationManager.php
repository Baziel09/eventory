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
                Tables\Columns\TextColumn::make('name')->label('Product')
                    ->url(fn (Item $record): string => route('filament.admin.resources.items.edit', ['record' => $record]))
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('pivot.cost_price')
                    ->label('Cost Price')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit.name')->label('Unit')
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('category')->relationship('category', 'name'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->form([
                        Forms\Components\Select::make('recordId')
                            ->label('Product')
                            ->options(Item::pluck('name', 'id'))
                            ->searchable()
                            ->createOptionForm([
                                Forms\Components\TextInput::make('name')->required(),
                                Forms\Components\Select::make('unit_id')
                                    ->label('Unit')
                                    ->options(Unit::pluck('name', 'id'))
                                    ->required(),
                                Forms\Components\Select::make('category_id')
                                    ->label('Category')
                                    ->options(Category::pluck('name', 'id'))
                                    ->required(),
                            ])
                            ->createOptionUsing(function (array $data): int {
                                $item = Item::create($data);
                                return $item->id;
                            }),
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