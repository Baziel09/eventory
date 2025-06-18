<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Gecombineerde Bestellingen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }

        .email-container {
            background-color: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        }

        .header {
            background-color: #343a40;
            color: #fff;
            padding: 20px;
            text-align: center;
        }

        .header h1 {
            margin: 0;
            font-size: 20px;
        }

        .content {
            padding: 20px;
        }

        .order-summary {
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }

        .order-item {
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }

        .order-item:last-child {
            border-bottom: none;
        }

        .order-header {
            font-weight: bold;
            margin-bottom: 10px;
            color: #2c3e50;
        }

        .item-list {
            margin-left: 20px;
        }

        .item {
            margin-bottom: 5px;
        }

        .footer {
            background-color: #f8f9fa;
            text-align: center;
            padding: 15px 20px;
            font-size: 13px;
            color: #666;
            border-top: 1px solid #e1e1e1;
        }

        .footer a {
            color: #007bff;
            text-decoration: none;
        }

        .footer a:hover {
            text-decoration: underline;
        }

        .consolidated-info {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #3498db;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>Gecombineerde Festival Bestellingen</h1>
        </div>
        
        <div class="content">
            {!! $emailMessage !!}
            
            <div class="consolidated-info">
                <h3>Overzicht Gecombineerde Bestelling</h3>
                <p><strong>Leverancier:</strong> {{ $orders->first()->supplier->name }}</p>
                <p><strong>Aantal bestellingen:</strong> {{ $orders->count() }}</p>
                <p><strong>Totaal aantal items:</strong> {{ $orders->sum(fn($order) => $order->orderItems->sum('quantity')) }}</p>
            </div>
            
            @foreach($orders as $order)
            <div class="order-summary">
                <div class="order-header">
                    Bestelling #{{ $order->id }} - {{ $order->vendor->name }}
                </div>
                <p><strong>Besteldatum:</strong> {{ $order->ordered_at->format('d/m/Y H:i') }}</p>
                @if($order->relationLoaded('user'))
                    <p><strong>Besteld door:</strong> {{ $order->user?->name ?? 'Onbekend' }}</p>
                @endif
                
                <div class="item-list">
                    @foreach($order->orderItems as $item)
                    <div class="item">
                        • {{ $item->item->name }} - {{ $item->quantity }} {{ $item->item->unit->name }}
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach
            
            <p>Een gedetailleerde PDF met alle bestellingen vindt u in de bijlage.</p>
        </div>
        
        <div class="footer">
            <p>Deze e-mail is automatisch gegenereerd via <strong>Eventory</strong>, het festivalbeheerplatform.</p>
            <p>Bezoek ons op <a href="https://eventory.app" target="_blank">eventory.app</a> voor meer informatie.</p>
        </div>
    </div>
</body>
</html>