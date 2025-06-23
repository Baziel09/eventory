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
use App\Filament\Resources\VendorResource\RelationManagers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Filament\Navigation\NavigationItem;
use Filament\Facades\Filament;
use App\Filament\Resources\VendorResource\RelationManagers\ItemRelationManager;



class VendorResource extends Resource
{
    protected static ?string $model = Vendor::class;
    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Voorraadbeheer';
    protected static ?string $label = 'Standen';
    protected static ?string $pluralLabel = 'Standen';
    protected static ?string $recordTitleAttribute = 'name';
    

    public static function getNavigationBadge(): ?string
    {
        $vendors = Vendor::whereHas('items', function ($query) {
            $query->whereColumn('quantity', '<', 'min_quantity');
        });

        if (auth()->user()->hasRole('voorraadbeheerder')) {
            $vendors = $vendors->whereHas('users', function ($query) {
                $query->where('user_id', auth()->id());
            });
        }

        return $vendors->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = Vendor::whereHas('items', function ($query) {
            $query->whereColumn('quantity', '<', 'min_quantity');
        })->count();

        return match (true) {
            $count === 0 => 'success',
            $count <= 5 => 'warning',
            default => 'danger',
        };
    }

    protected static ?string $navigationBadgeTooltip = 'Voorraad in gevaar';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('Naam')
                ->required(),
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
            Forms\Components\Section::make('Notities')
                ->schema([
                    Forms\Components\Textarea::make('notes')
                        ->label('')
                        ->placeholder('Voeg hier notities toe over deze stand')
                        ->rows(3)
                        ->columnSpanFull(),
                ])
                ->collapsible(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),

                Tables\Columns\TextColumn::make('location.name')
                    ->label('Locatie')
                    ->searchable(),

                Tables\Columns\TextColumn::make('low_stock_count')
                    ->label('Te lage voorraad')
                    ->badge()
                    ->color(fn (string $state): string => match (true) {
                        $state === '0' => 'success',
                        (int) $state <= 3 => 'warning',
                        default => 'danger',
                    })
                    ->getStateUsing(function (Vendor $record): string {
                        return (string) $record->items()
                            ->whereRaw('vendor_item_stock.quantity < vendor_item_stock.min_quantity')
                            ->count();
                    })
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query->withCount([
                            'items as low_stock_count' => function ($query) {
                                $query->whereRaw('vendor_item_stock.quantity < vendor_item_stock.min_quantity');
                            }
                        ])->orderBy('low_stock_count', $direction);
                    }),

                Tables\Columns\TextColumn::make('event.name')
                    ->label('Festival')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('notes')
                    ->label('Notities')
                    ->limit(25)
                    ->wrap()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Laatst gewijzigd')
                    ->dateTime()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_low_stock')
                    ->label('Heeft lage voorraad')
                    ->query(fn (Builder $query): Builder => 
                        $query->whereHas('items', function ($query) {
                            $query->whereRaw('vendor_item_stock.quantity < vendor_item_stock.min_quantity');
                        })
                    ),

                Tables\Filters\SelectFilter::make('location')
                    ->relationship('location', 'name')
                    ->label('Locatie op festival'),

            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(auth()->user()->hasRole('admin')),
            ]);
    }
    public static function getRelations(): array
    {
        return [
            RelationManagers\ItemRelationManager::class,
            // RelationManagers\StocksRelationManager::class,
        ];
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
