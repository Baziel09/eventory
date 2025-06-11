<?php

namespace App\Filament\Resources\VendorResource\RelationManagers;
use App\Models\Item;
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

    public function form(Form $form): Form
    {
        return $form->schema([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Item Name')
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
                Tables\Filters\SelectFilter::make('category')
                    ->relationship('category', 'name'),
            ])
            ->headerActions([]) // 🚫 No "Attach"
            ->actions([])       // 🚫 No "Edit" or "Detach"
            ->bulkActions([]);  // 🚫 No bulk delete
    }
}
    
// class ItemRelationManager extends RelationManager
// {
//     protected static string $relationship = 'items';

//     public function form(Form $form): Form
//     {
//         return $form
//             ->schema([
//                 Forms\Components\TextInput::make('name')
//                     ->required()
//                     ->maxLength(255),
//             ]);
//     }

//     public function table(Table $table): Table
//     {
//         return $table
//             ->recordTitleAttribute('name')
//             ->columns([
//                 Tables\Columns\TextColumn::make('name'),
//             ])
//             ->filters([
//                 //
//             ])
//             ->headerActions([
//                 Tables\Actions\CreateAction::make(),
//             ])
//             ->actions([
//                 Tables\Actions\EditAction::make(),
//                 Tables\Actions\DeleteAction::make(),
//             ])
//             ->bulkActions([
//                 Tables\Actions\BulkActionGroup::make([
//                     Tables\Actions\DeleteBulkAction::make(),
//                 ]),
//             ]);
//     }
// }
