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
use Awcodes\TableRepeater\Components\TableRepeater;
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
                    ->headers([
                        Header::make('item_id')
                            ->label('Item')
                            ->width('300px')
                            ->markAsRequired(),
                        Header::make('quantity')
                            ->label('Aantal')
                            ->width('120px')
                            ->markAsRequired(),
                        Header::make('unit')
                            ->label('Eenheid')
                            ->width('120px'),
                        Header::make('price')
                            ->label('Prijs per eenheid')
                            ->width('150px'),
                        Header::make('total')
                            ->label('Totaal')
                            ->width('150px'),
                    ])
                    ->schema([
                        Select::make('item_id')
                            ->label('Item')
                            ->options(function (callable $get) {
                                $supplierId = $get('../../supplier_id');
                                \Log::info('Getting items for supplier:', ['supplier_id' => $supplierId]);
                                
                                if (!$supplierId) {
                                    return [];
                                }
                                
                                $items = Item::whereHas('suppliers', function ($query) use ($supplierId) {
                                    $query->where('supplier_id', $supplierId);
                                })->with('unit')->get();
                                
                                \Log::info('Found items:', ['count' => $items->count(), 'items' => $items->pluck('name', 'id')->toArray()]);
                                
                                return $items->pluck('name', 'id');
                            })
                            ->required()
                            ->searchable()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                \Log::info('Item selected:', [
                                    'item_id' => $state,
                                    'supplier_id' => $get('../../supplier_id')
                                ]);
                                
                                if (!$state) {
                                    $set('price', 0);
                                    $set('unit_name', '');
                                    $set('total', 0);
                                    return;
                                }
                                
                                $supplierId = $get('../../supplier_id');
                                
                                // First, let's check if we can find the item at all
                                $item = Item::with('unit')->find($state);
                                if (!$item) {
                                    \Log::error('Item not found:', ['item_id' => $state]);
                                    return;
                                }
                                
                                \Log::info('Item loaded:', [
                                    'item' => $item->toArray(),
                                    'unit' => $item->unit ? $item->unit->toArray() : null
                                ]);
                                
                                // Set unit name
                                $unitName = $item->unit ? $item->unit->name : 'No unit';
                                $set('unit_name', $unitName);
                                \Log::info('Unit set to:', ['unit_name' => $unitName]);
                                
                                // Now let's try to get the price from pivot
                                if ($supplierId) {
                                    // Try direct DB query first
                                    $pivotData = \DB::table('supplier_items')
                                        ->where('supplier_id', $supplierId)
                                        ->where('item_id', $state)
                                        ->first();
                                    
                                    \Log::info('Pivot query result:', [
                                        'pivot_data' => $pivotData ? (array)$pivotData : null
                                    ]);
                                    
                                    if ($pivotData) {
                                        // Check what columns are available
                                        $columns = array_keys((array)$pivotData);
                                        \Log::info('Available pivot columns:', ['columns' => $columns]);
                                        
                                        // Try different possible column names
                                        $price = $pivotData->cost_price ?? $pivotData->price ?? 0;
                                        \Log::info('Price found:', ['price' => $price]);
                                        
                                        $set('price', $price);
                                        
                                        // Calculate total
                                        $quantity = floatval($get('quantity') ?? 1);
                                        $total = $price * $quantity;
                                        $set('total', $total);
                                        
                                        \Log::info('Total calculated:', [
                                            'quantity' => $quantity,
                                            'price' => $price,
                                            'total' => $total
                                        ]);
                                    } else {
                                        \Log::warning('No pivot data found');
                                        $set('price', 0);
                                        $set('total', 0);
                                    }
                                }
                            })
                            ->disabled(function (callable $get) {
                                return !$get('../../supplier_id');
                            })
                            ->helperText(function (callable $get) {
                                return !$get('../../supplier_id') ? 'Selecteer eerst een leverancier' : null;
                            }),

                        TextInput::make('quantity')
                            ->label('Aantal')
                            ->numeric()
                            ->default(1)
                            ->minValue(1)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $price = $get('price') ?? 0;
                                $quantity = floatval($state) ?: 1;
                                $set('total', $price * $quantity);
                            }),

                        TextInput::make('unit_name')
                            ->label('Eenheid')
                            ->disabled()
                            ->dehydrated(false),

                        TextInput::make('price')
                            ->label('Prijs')
                            ->numeric()
                            ->prefix('€')
                            ->step(0.01)
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set, callable $get) {
                                $quantity = $get('quantity') ?? 1;
                                $price = floatval($state) ?: 0;
                                $set('total', $price * $quantity);
                            }),

                        TextInput::make('total')
                            ->label('Totaal')
                            ->numeric()
                            ->prefix('€')
                            ->disabled()
                            ->dehydrated(false)
                            ->formatStateUsing(function ($state) {
                                return number_format(floatval($state), 2, ',', '.');
                            }),
                    ])
                    ->columnSpan('full')
                    ->streamlined()
                    ->renderHeader(true)
                    ->addActionLabel('Item toevoegen')
                    ->emptyLabel('Nog geen items toegevoegd aan deze bestelling.')
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        // Calculate grand total
                        $grandTotal = 0;
                        if (is_array($state)) {
                            foreach ($state as $item) {
                                $grandTotal += floatval($item['total'] ?? 0);
                            }
                        }
                        $set('grand_total', $grandTotal);
                    })
                    ->extraActions([
                        Forms\Components\Actions\Action::make('calculate_totals')
                            ->label('Herbereken totalen')
                            ->icon('heroicon-m-calculator')
                            ->color('gray')
                            ->action(function (callable $get, callable $set) {
                                $orderItems = $get('orderItems') ?? [];
                                $grandTotal = 0;
                                
                                foreach ($orderItems as $index => $item) {
                                    $quantity = floatval($item['quantity'] ?? 1);
                                    $price = floatval($item['price'] ?? 0);
                                    $total = $quantity * $price;
                                    
                                    $orderItems[$index]['total'] = $total;
                                    $grandTotal += $total;
                                }
                                
                                $set('orderItems', $orderItems);
                                $set('grand_total', $grandTotal);
                                
                                Notification::make()
                                    ->title('Totalen herberekend')
                                    ->success()
                                    ->send();
                            }),
                    ]),

                // Add this field after the TableRepeater to show the grand total
                Forms\Components\Card::make()
                    ->schema([
                        Forms\Components\Placeholder::make('grand_total_display')
                            ->label('Totaal bestelling')
                            ->content(function (callable $get) {
                                $grandTotal = $get('grand_total') ?? 0;
                                return '€ ' . number_format(floatval($grandTotal), 2, ',', '.');
                            })
                            ->extraAttributes([
                                'class' => 'text-xl font-bold text-primary-600',
                            ]),
                            
                        Forms\Components\Hidden::make('grand_total')
                            ->default(0),
                    ])
                    ->columnSpan('full'),
                    
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('send_email')
                        ->label('Verstuur naar leverancier')
                        ->icon('heroicon-o-envelope')
                        ->color('primary')
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
                        'warning' => 'pending',
                        'success' => 'confirmed',
                        'primary' => 'delivered',
                        'danger' => 'cancelled',
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
                    ->color('success')
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
                    ->color('danger')
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
                    ->color('primary')
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
                    ->color('success')
                    ->visible(fn (Order $record) => $record->status === 'confirmed')
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
                    Tables\Actions\DeleteBulkAction::make(),
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
                            ->color(function ($state) {
                                return match ($state) {
                                    'pending' => 'warning',
                                    'confirmed' => 'success',
                                    'sent' => 'info',
                                    'delivered' => 'primary',
                                    'cancelled' => 'danger',
                                    default => 'gray',
                                };
                            })
                            ->formatStateUsing(function ($state) {
                                return match ($state) {
                                    'pending' => 'In afwachting',
                                    'confirmed' => 'Bevestigd',
                                    'sent' => 'Verstuurd',
                                    'delivered' => 'Geleverd',
                                    'cancelled' => 'Geannuleerd',
                                    default => $state,
                                };
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