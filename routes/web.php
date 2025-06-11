<?php

use Illuminate\Support\Facades\Route;
use App\Models\Order;
use App\Services\OrderPdfService;
use Illuminate\Support\Facades\Response;

Route::get('/orders/{order}/preview-pdf', function (Order $order, OrderPdfService $pdfService) {
    $path = $pdfService->generateOrderPdf($order);

    return Response::file($path, [
        'Content-Type' => 'application/pdf',
        'Content-Disposition' => 'inline; filename="order-preview.pdf"',
    ]);
})->name('orders.preview.pdf');