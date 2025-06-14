<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Mail\OrderMail;
use App\Services\OrderPdfService;
use Filament\Actions;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Mail;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('cancel_order')
                ->label('Annuleren')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Order $record) => in_array($record->status, ['pending', 'confirmed']))
                ->requiresConfirmation()
                ->action(function (Order $record) {
                    $record->update(['status' => 'cancelled']);

                    $this->refreshFormData([
                        'status'
                    ]);
                    
                    Notification::make()
                        ->title('Bestelling geannuleerd')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('restore_order')
                ->label('Herstellen')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->visible(fn (Order $record) => $record->status === 'cancelled')
                ->action(function (Order $record) {
                    $record->update(['status' => 'pending']);

                    $this->refreshFormData([
                        'status'
                    ]);
                    
                    Notification::make()
                        ->title('Bestelling hersteld')
                        ->success()
                        ->send();
                }),

            Actions\Action::make('confirm')
                ->label('Bevestigen')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (Order $record) => $record->status === 'pending')
                ->action(function (Order $record) {
                    $record->update(['status' => 'confirmed']);

                    $this->refreshFormData([
                        'status'
                    ]);
                    
                    Notification::make()
                        ->title('Bestelling bevestigd')
                        ->success()
                        ->send();
                }),
                
            Actions\Action::make('send_email')
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
                            return $this->getDefaultEmailTemplate($record);
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
                        
                    Toggle::make('include_pdf')
                        ->label('PDF bijvoegen')
                        ->default(true)
                        ->helperText('Voegt automatisch een PDF van de bestelling toe als bijlage'),
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

                        $this->refreshFormData([
                            'status'
                        ]);
                        
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

            Actions\Action::make('mark_as_delivered')
                ->label('Geleverd')
                ->icon('heroicon-o-truck')
                ->color('green')
                ->visible(fn (Order $record) => $record->status === 'sent')
                ->action(function (Order $record) {
                    \DB::transaction(function () use ($record) {
                        $record->update(['status' => 'delivered']);
            
                        $delivery = \App\Models\Delivery::create([
                            'order_id' => $record->id,
                            'delivered_at' => now(),
                            'user_id' => auth()->id() 
                        ]);
            
                        foreach ($record->orderItems as $orderItem) {
                            // Create delivery item
                            $delivery->items()->create([
                                'item_id' => $orderItem->item_id,
                                'delivered_quantity' => $orderItem->quantity
                            ]);

                            $vendorItem = $record->vendor->items()->where('item_id', $orderItem->item_id)->first();
                            
                            if ($vendorItem) {
                                $record->vendor->items()->updateExistingPivot($orderItem->item_id, [
                                    'quantity' => $vendorItem->pivot->quantity + $orderItem->quantity
                                ]);
                            } else {
                                $record->vendor->items()->attach($orderItem->item_id, [
                                    'quantity' => $orderItem->quantity
                                ]);
                            }
                        }
                    });
                    
                    $this->refreshFormData([
                        'status'
                    ]);
                    
                    Notification::make()
                        ->title('Bestelling geleverd')
                        ->body('De voorraad is succesvol bijgewerkt')
                        ->success()
                        ->send();
                }),
    ];
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
}
