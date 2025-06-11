<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;

class OrderPdfService
{
    public function generateOrderPdf(Order $order): string
    {
        // Load the order with all necessary relationships
        $order->load(['vendor', 'supplier', 'orderItems.item.category', 'orderItems.item.unit']);
        
        // Generate PDF
        $pdf = Pdf::loadView('pdf.order', compact('order'));
        
        // Create temporary file
        $filename = 'order_' . $order->id . '_' . time() . '.pdf';
        $path = storage_path('app/public/temp/' . $filename);
        
        // Make sure temp directory exists
        if (!file_exists(storage_path('app/public/temp'))) {
            mkdir(storage_path('app/public/temp'), 0755, true);
        }
        
        // Save PDF to temporary location
        $pdf->save($path);
        
        return $path;
    }
    
    public function downloadOrderPdf(Order $order)
    {
        $order->load(['vendor', 'supplier', 'orderItems.item.category', 'orderItems.item.unit']);
        
        $pdf = Pdf::loadView('pdf.order', compact('order'));
        
        return $pdf->download("bestelling-{$order->id}.pdf");
    }
}