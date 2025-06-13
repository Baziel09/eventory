<?php

namespace App\Filament\Resources\SupplierResource\RelationManagers;

use App\Models\Item;
use App\Models\Vendor;
use App\Services\OrderPdfService;
use App\Mail\OrderMail;
use App\Models\Order;
use Filament\Forms;
use Filament\Tables;
use Filament\Resources\RelationManagers\RelationManager;
use Illuminate\Support\Facades\Mail;
use Filament\Notifications\Notification;
use Filament\Tables\Table;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Form;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;

class OrderRelationManager extends RelationManager
{
    protected static string $relationship = 'orders';
    protected static ?string $title = 'Bestellingen';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Grid::make(2)
                    ->schema([
                        Select::make('vendor_id')
                            ->label('Stand')
                            ->options(Vendor::all()->pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        DateTimePicker::make('ordered_at')
                            ->label('Besteldatum')
                            ->default(now())
                            ->required(),

                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'pending' => 'In afwachting',
                                'confirmed' => 'Bevestigd',
                                'sent' => 'Verstuurd',
                                'delivered' => 'Geleverd',
                                'cancelled' => 'Geannuleerd',
                            ])
                            ->default('pending')
                            ->required(),
                    ]),

                Repeater::make('orderItems')
                    ->relationship('orderItems')
                    ->label('Producten')
                    ->schema([
                        Select::make('item_id')
                            ->label('Product')
                            ->options(function (callable $get) {
                                $supplierId = $this->getOwnerRecord()->id;
                                if (!$supplierId) return [];

                                return Item::whereHas('suppliers', fn ($q) => $q->where('supplier_id', $supplierId))
                                    ->with('category')
                                    ->get()
                                    ->mapWithKeys(fn ($item) => [
                                        $item->id => $item->name . ' (' . $item->category->name . ')',
                                    ]);
                            })
                            ->required()
                            ->searchable(),

                        TextInput::make('quantity')
                            ->label('Aantal')
                            ->numeric()
                            ->required()
                            ->minValue(1)
                            ->default(1),
                    ])
                    ->columns(2)
                    ->defaultItems(1)
                    ->addActionLabel('Product toevoegen'),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('Bestelnummer')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('vendor.name')
                    ->label('Stand')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('ordered_at')
                    ->label('Besteldatum')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'info' => 'sent',
                        'primary' => 'delivered',
                        'danger' => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'pending' => 'In afwachting',
                        'confirmed' => 'Bevestigd',
                        'sent' => 'Verstuurd',
                        'delivered' => 'Geleverd',
                        'cancelled' => 'Geannuleerd',
                        default => $state,
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'pending' => 'In afwachting',
                        'confirmed' => 'Bevestigd',
                        'sent' => 'Verstuurd',
                        'delivered' => 'Geleverd',
                        'cancelled' => 'Geannuleerd',
                    ]),

                Filter::make('show_cancelled')
                    ->label('Geannuleerde tonen')
                    ->form([
                        Toggle::make('value')
                            ->label('Toon geannuleerde bestellingen')
                            ->default(false),
                    ])
                    ->query(function ($query, array $data) {
                        if (!($data['value'] ?? false)) {
                            $query->where('status', '!=', 'cancelled');
                        }
                    }),
            ])
            ->headerActions([
                Tables\Actions\Action::make('new_order')
                    ->label('Nieuwe bestelling')
                    ->icon('heroicon-o-plus')
                    ->url(fn () => route('filament.admin.resources.orders.create', [
                        'supplier_id' => $this->getOwnerRecord()->id
                    ]))
            ])
            ->actions([
                // Tables\Actions\EditAction::make()
                //     ->label(''),
                // Tables\Actions\ViewAction::make()
                //     ->label(''),

                // Tables\Actions\Action::make('confirm')
                //     ->label('Bevestigen')
                //     ->icon('heroicon-o-check-circle')
                //     ->color('success')
                //     ->visible(fn (Order $record) => $record->status === 'pending')
                //     ->action(fn (Order $record) => $record->update(['status' => 'confirmed'])),

                // Tables\Actions\Action::make('cancel')
                //     ->label('Annuleren')
                //     ->icon('heroicon-o-x-circle')
                //     ->color('danger')
                //     ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed']))
                //     ->requiresConfirmation()
                //     ->action(fn (Order $record) => $record->update(['status' => 'cancelled'])),

                Tables\Actions\Action::make('go_to_order')
                    ->label('Ga naar bestelling')
                    ->url(fn (Order $record) => route('filament.admin.resources.orders.edit', ['record' => $record]))
                    ->color('primary')
                    ->icon('heroicon-o-arrow-top-right-on-square'),
                    
            ]);
    }
}
