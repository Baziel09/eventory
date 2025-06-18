<?php

namespace App\Services;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Collection;

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

    public function generateConsolidatedOrderPdf(Collection $orders): string
    {
        // Eager load all necessary relationships
        $orders->load([
            'supplier',
            'vendor.event',
            'user',
            'orderItems.item.category',
            'orderItems.item.unit'
        ]);

        $supplier = $orders->first()->supplier;
        $orderNumbers = $orders->pluck('id')->join('_');
        
        // Generate HTML with proper error handling
        try {
            $html = view('pdf.consolidated-order', ['orders' => $orders])->render();
        } catch (\Exception $e) {
            \Log::error('Failed to render consolidated order PDF view', [
                'error' => $e->getMessage(),
                'orders' => $orders->pluck('id')
            ]);
            throw $e;
        }
        
        // Generate PDF
        $pdf = Pdf::loadHTML($html);
        $pdf->setPaper('A4', 'portrait');
        
        // Create unique filename with timestamp
        $filename = "gecombineerde_bestelling_{$supplier->id}_{$orderNumbers}_" . now()->format('YmdHis') . ".pdf";
        $tempDir = storage_path('app/temp');
        $path = "{$tempDir}/{$filename}";
        
        // Ensure temp directory exists
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        // Save PDF
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