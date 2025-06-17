<?php

namespace App\Filament\Resources\VendorResource\RelationManagers;

use App\Models\Item;
use App\Models\Unit;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Tables\Columns\BadgeColumn;
class ItemRelationManager extends RelationManager
{
   protected static string $relationship = 'items';
   protected static ?string $title = 'Voorraad';

   public function form(Form $form): Form
   {
       return $form->schema([
       ]);
   }


   public function table(Table $table): Table
   {
       return $table
       ->heading('Voorraad')
           ->columns([
               Tables\Columns\TextColumn::make('name')->label('Product naam')
                   ->url(fn (Item $record): string => route('filament.admin.resources.items.edit', ['record' => $record]))
                   ->sortable()
                   ->searchable(),
               Tables\Columns\TextColumn::make('category.name')->label('Categorie')
                   ->sortable()
                   ->searchable(),
               Tables\Columns\TextColumn::make('unit.name')->label('Eenheid')
                   ->sortable()
                   ->searchable(),
               Tables\Columns\TextColumn::make('created_at')
                   ->label('Aangemaakt')->dateTime()
                   ->toggleable(isToggledHiddenByDefault: true),
               Tables\Columns\TextColumn::make('updated_at')
                   ->label('Laatst gewijzigd')->dateTime()
                   ->toggleable(isToggledHiddenByDefault: true),
               Tables\Columns\TextColumn::make('quantity')
                   ->label('Voorraad')
                   ->sortable()
                   ->searchable(),
                Tables\Columns\TextColumn::make('min_quantity')
                   ->label('Minimum voorraad')
                   ->sortable()
                   ->searchable(),
                   

                   BadgeColumn::make('quantity')
                       ->label('voorraad')
                       ->colors([
                            'red' => fn ($record) => $record->pivot->quantity < $record->pivot->min_quantity,
                            'emerald' => fn ($record) => $record->pivot->quantity >= $record->pivot->min_quantity,
                            'yellow' => fn ($record) => $record->pivot->quantity < ($record->pivot->min_quantity * 1.5) && $record->pivot->quantity >= $record->pivot->min_quantity,
                       ])
                       ->formatStateUsing(fn ($state) => $state),
           ])
                ->filters([
               Tables\Filters\TrashedFilter::make(),
               Tables\Filters\SelectFilter::make('category')
                   ->relationship('category', 'name'),
               Tables\Filters\SelectFilter::make('unit')
                   ->relationship('unit', 'name'),
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
                    Forms\Components\TextInput::make('quantity')
                        ->numeric()
                        ->required(),
                        
                ]),
        ])
        
        ->actions([
            Tables\Actions\EditAction::make()
                ->label('Edit')
                ->form([
                    Forms\Components\TextInput::make('quantity')
                        ->label('Quantiteit')
                        ->numeric()
                        ->required(),
                    Forms\Components\TextInput::make('min_quantity')
                        ->label('Minimum voorraad')
                        ->numeric()
                        ->required()
                        ->default(0),
                ]),
        
            Tables\Actions\Action::make('increase')
                ->label('+1')
                ->color('success')
                ->action(function (Item $record) {
                    $record->pivot->quantity += 1;
                    $record->pivot->save();
                }),
        
            Tables\Actions\Action::make('decrease')
                ->label('-1')
                ->color('danger')
                ->action(function (Item $record) {
                    $record->pivot->quantity = max(0, $record->pivot->quantity - 1);
                    $record->pivot->save();
                }),
        
            Tables\Actions\Action::make('addCustom')
                ->label('+ Custom')
                ->color('warning')
                ->form([
                    Forms\Components\TextInput::make('amount')->numeric()->required(),
                ])
                ->action(function (array $data, Item $record) {
                    $record->pivot->quantity += (int) $data['amount'];
                    $record->pivot->save();
                }),
            Tables\Actions\Action::make('order')
                ->label('Bestellen')
                ->color('success')
                ->icon('heroicon-o-shopping-cart')
                ->url(fn ($record) => route('filament.admin.resources.orders.create', [
                    'supplier_id' => $record->id,
                    'item_id' => $this->getOwnerRecord()->id 
                ])),
    
        ])
           ->bulkActions([
               Tables\Actions\DeleteBulkAction::make(),
               Tables\Actions\ForceDeleteBulkAction::make(),
               Tables\Actions\RestoreBulkAction::make(),
           ]);
   }
}
