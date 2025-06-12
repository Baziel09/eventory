<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Vendor;
use App\Models\Supplier;
use App\Models\Item;
use App\Models\OrderItem;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Infolists\Infolist;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\ViewEntry;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\Section as FormSection;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Enums\VerticalAlignment;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderMail;
use App\Services\OrderPdfService;
// use Awcodes\TableRepeater\Components\TableRepeater;
use Icetalker\FilamentTableRepeater\Forms\Components\TableRepeater;
use Awcodes\TableRepeater\Header;


class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    
    protected static ?string $navigationLabel = 'Bestellingen';
    
    protected static ?string $modelLabel = 'Bestelling';
    
    protected static ?string $pluralModelLabel = 'Bestellingen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Grid::make(2)
                            ->schema([
                                Select::make('vendor_id')
                                    ->label('Stand')
                                    ->options(Vendor::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable(),
                                    
                                Select::make('supplier_id')
                                    ->label('Leverancier')
                                    ->options(Supplier::all()->pluck('name', 'id'))
                                    ->required()
                                    ->searchable()
                                    ->reactive()
                                    ->afterStateUpdated(function ($state, callable $set) {
                                        // Reset order items when supplier changes
                                        $set('orderItems', []);
                                    }),
                                    
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
                    ]),

                TableRepeater::make('orderItems')
                    ->label('Bestelde Items')
                    ->relationship('orderItems')
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(function (callable $get) {
                                $supplierId = $get('../../supplier_id');
                                if (!$supplierId) {
                                    return [];
                                }
                                
                                // Get items that this supplier can provide
                                return Item::whereHas('suppliers', function ($query) use ($supplierId) {
                                    $query->where('supplier_id', $supplierId);
                                })->with(['category', 'unit'])->get()
                                ->mapWithKeys(function ($item) {
                                    return [$item->id => "{$item->name} ({$item->category->name}) - {$item->unit->name}"];
                                });
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Reset quantity when item changes
                                $set('quantity', null);
                                
                                // Get cost price from pivot table
                                $supplierId = $get('../../supplier_id');
                                if ($state && $supplierId) {
                                    $supplierItem = \DB::table('supplier_item')
                                        ->where('supplier_id', $supplierId)
                                        ->where('item_id', $state)
                                        ->first();
                                    
                                    if ($supplierItem) {
                                        $set('cost_price', $supplierItem->cost_price);
                                    }
                                }
                                
                                // Get unit name from item
                                $item = Item::find($state);
                                if ($item) {
                                    $set('unit_name', $item->unit->name);
                                }
                            })
                            ->disabled(fn (callable $get) => !$get('../../supplier_id')),
                            
                        TextInput::make('quantity')
                            ->label('Aantal')
                            ->numeric()
                            ->minValue(1)
                            ->step(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                // Calculate line total when quantity changes
                                $costPrice = $get('cost_price');
                                if ($state && $costPrice) {
                                    $set('line_total', $state * $costPrice);
                                }
                            }),
                            
                        TextInput::make('cost_price')
                            ->label('Kostprijs')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->disabled()
                            ->dehydrated(false), // Don't save this field
                            
                        TextInput::make('unit_name')
                            ->label('Eenheid')
                            ->disabled()
                            ->dehydrated(false) // Don't save this field
                            ->afterStateHydrated(function ($component, $state, $record) {
                                if ($record && $record->item) {
                                    $component->state($record->item->unit->name ?? '');
                                }
                            }),
                            
                        TextInput::make('line_total')
                            ->label('Totaal')
                            ->numeric()
                            ->step(0.01)
                            ->prefix('€')
                            ->disabled()
                            ->dehydrated(false), // Don't save this field
                    ])
                    ->columnSpan('full')
                    ->reorderable(false)
                    ->cloneable()
                    ->collapsible()
                    ->itemLabel(fn (array $state): ?string => 
                        isset($state['item_id']) ? Item::find($state['item_id'])?->name : 'Nieuw item'
                    )
                    ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                        // Only save the fields that should be persisted
                        return [
                            'item_id' => $data['item_id'],
                            'quantity' => $data['quantity'],
                        ];
                    })
                    ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                        // Only save the fields that should be persisted
                        return [
                            'item_id' => $data['item_id'],
                            'quantity' => $data['quantity'],
                        ];
                    })
                    ->afterStateHydrated(function ($component, $state, $record) {
                        // Populate calculated fields when editing
                        if ($record && $record->orderItems) {
                            $supplierId = $record->supplier_id;
                            $hydratedState = [];
                            
                            foreach ($record->orderItems as $index => $orderItem) {
                                $itemData = [
                                    'item_id' => $orderItem->item_id,
                                    'quantity' => $orderItem->quantity,
                                    'unit_name' => $orderItem->item->unit->name ?? '',
                                ];
                                
                                // Get cost price from pivot
                                $supplierItem = \DB::table('supplier_item')
                                    ->where('supplier_id', $supplierId)
                                    ->where('item_id', $orderItem->item_id)
                                    ->first();
                                
                                if ($supplierItem) {
                                    $itemData['cost_price'] = $supplierItem->cost_price;
                                    $itemData['line_total'] = $orderItem->quantity * $supplierItem->cost_price;
                                }
                                
                                $hydratedState[] = $itemData;
                            }
                            
                            $component->state($hydratedState);
                        }
                    })
                    ->colStyles([
                        'item_id' => 'width: 40%;',
                        'quantity' => 'width: 15%;',
                        'cost_price' => 'width: 15%;',
                        'unit_name' => 'width: 15%;',
                        'line_total' => 'width: 15%;',
                    ]),

                // Add total calculation section
                Forms\Components\Section::make('Totaal')
                    ->schema([
                        Forms\Components\Placeholder::make('total_amount')
                            ->label('Totaalbedrag')
                            ->extraAttributes([
                                'class' => 'text-xl font-bold text-primary-600',
                            ])
                            ->content(function (callable $get) {
                                $orderItems = $get('orderItems') ?? [];
                                $total = 0;
                                
                                foreach ($orderItems as $item) {
                                    if (isset($item['line_total']) && is_numeric($item['line_total'])) {
                                        $total += $item['line_total'];
                                    }
                                }
                                
                                return '€ ' . number_format($total, 2, ',', '.');
                            }),
                    ])
                    ->columnSpan('full'),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('confirm')
                        ->label('Bevestigen')
                        ->icon('heroicon-o-check-circle')
                        ->color('emerald')
                       ->visible(fn (?Order $record) => $record && $record->exists && $record->status === 'pending')
                        ->action(function (Order $record) {
                            $record->update(['status' => 'confirmed']); 
                            
                            Notification::make()
                                ->title('Bestelling bevestigd')
                                ->success()
                                ->send();
                        }),

                    Forms\Components\Actions\Action::make('cancel')
                        ->label('Annuleren')
                        ->icon('heroicon-o-x-circle')
                        ->color('red')
                        ->visible(fn (?Order $record) => $record && $record->exists && in_array($record->status, ['pending', 'confirmed']))
                        ->requiresConfirmation()
                        ->action(function (Order $record) {
                            $record->update(['status' => 'cancelled']);
                            
                            Notification::make()
                                ->title('Bestelling geannuleerd')
                                ->success()
                                ->send();
                        }),
                    
                    Forms\Components\Actions\Action::make('send_email')
                        ->label('Verstuur naar leverancier')
                        ->icon('heroicon-o-envelope')
                        ->color('blue')
                        ->visible(fn (?Order $record) => $record && $record->exists && $record->status === 'confirmed')
                        ->form([
                            TextInput::make('subject')
                                ->label('Onderwerp')
                                ->default(fn (Order $record) => "Bestelling #{$record->id} - {$record->vendor->name}")
                                ->required(),
                                
                            RichEditor::make('message')
                                ->label('Bericht')
                                ->default(function (Order $record) {
                                    return self::getDefaultEmailTemplate($record);
                                })
                                ->required()
                                ->toolbarButtons([
                                    'bold',
                                    'italic',
                                    'underline',
                                    'strike',
                                    'bulletList',
                                    'orderedList',
                                    'h2',
                                    'h3',
                                    'link',
                                    'undo',
                                    'redo',
                                ]),
                                
                            Forms\Components\Toggle::make('include_pdf')
                                ->label('PDF bijvoegen')
                                ->default(true)
                                ->helperText('Voegt automatisch een PDF van de bestelling toe als bijlage'),
                            
                            Forms\Components\Actions::make([
                                Forms\Components\Actions\Action::make('preview_pdf')
                                    ->label('Voorbeeld PDF')
                                    ->icon('heroicon-o-eye')
                                    ->color('gray')
                                    ->url(fn (Order $record) => route('orders.preview.pdf', $record))
                                    ->openUrlInNewTab()
                                    ->visible(fn (Order $record) => true),
                            ]),
                        ])
                        ->action(function (Order $record, array $data) {
                            try {
                                // Generate PDF if requested
                                $pdfPath = null;
                                if ($data['include_pdf']) {
                                    $pdfService = new OrderPdfService();
                                    $pdfPath = $pdfService->generateOrderPdf($record);
                                }
                                
                                // Send email
                                Mail::to($record->supplier->contact_email)
                                    ->send(new OrderMail($record, $data['subject'], $data['message'], $pdfPath));
                                
                                // Clean up temporary PDF file
                                if ($pdfPath && file_exists($pdfPath)) {
                                    unlink($pdfPath);
                                }

                                // Update status
                                $record->status = 'sent';
                                $record->save();
                                
                                Notification::make()
                                    ->title('Email verstuurd')
                                    ->body("De bestelling is verstuurd naar {$record->supplier->name} ({$record->supplier->contact_email})")
                                    ->success()
                                    ->send();
                                    
                            } catch (\Exception $e) {
                                Notification::make()
                                    ->title('Fout bij versturen email')
                                    ->body('Er is een fout opgetreden: ' . $e->getMessage())
                                    ->danger()
                                    ->send();
                            }
                        }),

                    Forms\Components\Actions\Action::make('deliver')
                        ->label('Bevestig levering')
                        ->icon('heroicon-o-truck')
                        ->color('green')
                        ->visible(fn (?Order $record) => $record && $record->exists && $record->status === 'sent')
                        ->action(function (Order $record) {
                            $record->update(['status' => 'delivered']);
                            
                            Notification::make()
                                ->title('Bestelling geleverd')
                                ->success()
                                ->send();
                        }),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Bestelnummer')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('vendor.name')
                    ->label('Stand')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('supplier.name')
                    ->label('Leverancier')
                    ->sortable()
                    ->searchable(),
                    
                TextColumn::make('ordered_at')
                    ->label('Besteldatum')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                    
                BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'amber' => 'pending',
                        'emerald' => 'confirmed',
                        'blue' => 'sent',
                        'green' => 'delivered',
                        'red' => 'cancelled',
                    ])
                    ->formatStateUsing(function ($state) {
                        return match ($state) {
                            'pending' => 'In afwachting',
                            'confirmed' => 'Bevestigd',
                            'delivered' => 'Geleverd',
                            'cancelled' => 'Geannuleerd',
                            default => $state,
                        };
                    }),
                    
                TextColumn::make('orderItems')
                    ->label('Aantal items')
                    ->formatStateUsing(function ($record) {
                        // Explicitly load the relationship count
                        return $record->orderItems()->count();
                    })
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->sortable(),
                    
                TextColumn::make('created_at')
                    ->label('Aangemaakt')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->multiple()
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
                    ->query(function (Builder $query, array $data) {
                        if (!($data['value'] ?? false)) {
                            $query->where('status', '!=', 'cancelled');
                        }
                    }),
                    
                SelectFilter::make('vendor_id')
                    ->label('Stand')
                    ->options(Vendor::all()->pluck('name', 'id')),
                    
                SelectFilter::make('supplier_id')
                    ->label('Leverancier')
                    ->options(Supplier::all()->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label(''),
                Tables\Actions\EditAction::make()
                    ->label(''),
                
                Action::make('confirm')
                    ->label('Bevestigen')
                    ->icon('heroicon-o-check-circle')
                    ->color('emerald')
                    ->visible(fn (Order $record) => $record->status === 'pending')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'confirmed']);
                        
                        Notification::make()
                            ->title('Bestelling bevestigd')
                            ->success()
                            ->send();
                    }),
                    
                Action::make('cancel')
                    ->label('Annuleren')
                    ->icon('heroicon-o-x-circle')
                    ->color('red')
                    ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed']))
                    ->requiresConfirmation()
                    ->action(function (Order $record) {
                        $record->update(['status' => 'cancelled']);
                        
                        Notification::make()
                            ->title('Bestelling geannuleerd')
                            ->success()
                            ->send();
                    }),
                    
                Action::make('send_email')
                    ->label('Verstuur')
                    ->icon('heroicon-o-envelope')
                    ->color('blue')
                    ->visible(fn (Order $record) => $record->status === 'confirmed')
                    ->form([
                        TextInput::make('subject')
                            ->label('Onderwerp')
                            ->default(fn (Order $record) => "Bestelling #{$record->id} - {$record->vendor->name}")
                            ->required(),
                            
                        RichEditor::make('message')
                            ->label('Bericht')
                            ->default(function (Order $record) {
                                return self::getDefaultEmailTemplate($record);
                            })
                            ->required()
                            ->toolbarButtons([
                                'bold',
                                'italic',
                                'underline',
                                'strike',
                                'bulletList',
                                'orderedList',
                                'h2',
                                'h3',
                                'link',
                                'undo',
                                'redo',
                            ]),
                            
                        Forms\Components\Toggle::make('include_pdf')
                            ->label('PDF bijvoegen')
                            ->default(true)
                            ->helperText('Voegt automatisch een PDF van de bestelling toe als bijlage'),
                        
                        Forms\Components\Actions::make([
                            Forms\Components\Actions\Action::make('preview_pdf')
                                ->label('Voorbeeld PDF')
                                ->icon('heroicon-o-eye')
                                ->color('gray')
                                ->url(fn (Order $record) => route('orders.preview.pdf', $record))
                                ->openUrlInNewTab()
                                ->visible(fn (Order $record) => true),
                        ]),
                    ])
                    ->action(function (Order $record, array $data) {
                        try {
                            // Generate PDF if requested
                            $pdfPath = null;
                            if ($data['include_pdf']) {
                                $pdfService = new OrderPdfService();
                                $pdfPath = $pdfService->generateOrderPdf($record);
                            }
                            
                            // Send email
                            Mail::to($record->supplier->contact_email)
                                ->send(new OrderMail($record, $data['subject'], $data['message'], $pdfPath));
                            
                            // Clean up temporary PDF file
                            if ($pdfPath && file_exists($pdfPath)) {
                                unlink($pdfPath);
                            }

                            // Update status
                                $record->status = 'sent';
                                $record->save();
                            
                            Notification::make()
                                ->title('Email verstuurd')
                                ->body("De bestelling is verstuurd naar {$record->supplier->name} ({$record->supplier->contact_email})")
                                ->success()
                                ->send();
                                
                        } catch (\Exception $e) {
                            Notification::make()
                                ->title('Fout bij versturen email')
                                ->body('Er is een fout opgetreden: ' . $e->getMessage())
                                ->danger()
                                ->send();
                        }
                    }),
                    
                Action::make('mark_as_delivered')
                    ->label('Geleverd')
                    ->icon('heroicon-o-truck')
                    ->color('green')
                    ->visible(fn (Order $record) => $record->status === 'sent')
                    ->action(function (Order $record) {
                        $record->update(['status' => 'delivered']);
                        
                        Notification::make()
                            ->title('Bestelling geleverd')
                            ->success()
                            ->send();
                    }),
                    
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // cancel bulk action
                    Tables\Actions\BulkAction::make('cancel')
                        ->label('Annuleren')
                        ->icon('heroicon-o-x-circle')
                        ->color('red')
                        ->requiresConfirmation()
                        ->action(function (Collection $records) {
                            foreach ($records as $record) {
                                $record->update(['status' => 'cancelled']);
                            }
                        })
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Section::make('Bestelgegevens')
                    ->schema([
                        TextEntry::make('id')
                            ->label('Bestelnummer'),
                            
                        TextEntry::make('vendor.name')
                            ->label('Stand'),
                            
                        TextEntry::make('supplier.name')
                            ->label('Leverancier'),
                            
                        TextEntry::make('supplier.contact_email')
                            ->label('Contact email leverancier'),
                            
                        TextEntry::make('ordered_at')
                            ->label('Besteldatum')
                            ->dateTime('d/m/Y H:i'),
                            
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($state): string => match (strtolower($state))  {
                                    'amber' => 'pending',
                                    'emerald' => 'confirmed',
                                    'blue' => 'sent',
                                    'green' => 'delivered',
                                    'red' => 'cancelled',
                                    default => 'gray',
                                }),
                         
                    ])
                    ->columns(2),

                Section::make('Bestelde Items')
                    ->schema([
                        Grid::make(4)
                            ->schema([
                                TextEntry::make('')->label('Item')->placeholder('')->columnSpan(1),
                                TextEntry::make('')->label('Categorie')->placeholder('')->columnSpan(1),
                                TextEntry::make('')->label('Aantal')->placeholder('')->columnSpan(1),
                                TextEntry::make('')->label('Eenheid')->placeholder('')->columnSpan(1),
                            ]),

                        RepeatableEntry::make('orderItems')
                            ->label('')
                            ->schema([
                                TextEntry::make('item.name')->label(''),
                                TextEntry::make('item.category.name')->label(''),
                                TextEntry::make('quantity')->label(''),
                                TextEntry::make('item.unit.name')->label(''),
                            ])
                            ->columns(4),
                    ]),
            ]);
    }

    private static function getDefaultEmailTemplate(Order $record): string
    {
        $itemsList = $record->orderItems->map(function ($orderItem) {
            return "• {$orderItem->item->name} - {$orderItem->quantity} {$orderItem->item->unit->name}";
        })->join('<br>');

        return "
            <p>Beste {$record->supplier->name},</p>
            
            <p>Hierbij ontvangt u een nieuwe bestelling van {$record->vendor->name} voor het festival.</p>
            
            <p><strong>Bestelgegevens:</strong><br>
            Bestelnummer: #{$record->id}<br>
            Besteldatum: " . $record->ordered_at->format('d/m/Y H:i') . "<br>
            Stand: {$record->vendor->name}</p>
            
            <p><strong>Bestelde items:</strong><br>
            {$itemsList}</p>
            
            <p>Een gedetailleerde PDF van de bestelling vindt u in de bijlage.</p>
            
            <p>Kunt u de levering bevestigen en de verwachte levertijd doorgeven?</p>
            
            <p>Met vriendelijke groet,<br>
            Festival Management Team</p>
        ";
    }

    public static function getRelations(): array
    {
        return [
            // RelationManagers\DeliveriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            // 'view' => Pages\ViewOrder::route('/{record}'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}