<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Bestelling #{{ $order->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            font-size: 12px;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #333;
        }
        .company-info {
            margin-bottom: 30px;
        }
        .order-info {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .order-details {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
        }
        .detail-box {
            width: 48%;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .items-table th,
        .items-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .items-table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .items-table tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-confirmed { background-color: #d4edda; color: #155724; }
        .status-delivered { background-color: #cce5ff; color: #004085; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        h1 { font-size: 24px; margin: 0; }
        h2 { font-size: 18px; margin: 15px 0 10px 0; }
        h3 { font-size: 14px; margin: 10px 0 5px 0; }
        p { margin: 5px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>FESTIVAL BESTELLING</h1>
        <p>Bestelnummer: #{{ $order->id }}</p>
    </div>

    <div class="order-info">
        <h2>Bestelgegevens</h2>
        <p><strong>Besteldatum:</strong> {{ $order->ordered_at->format('d/m/Y H:i') }}</p>
        <p><strong>Status:</strong> 
            <span class="status-badge status-{{ $order->status }}">
                @switch($order->status)
                    @case('pending') In afwachting @break
                    @case('confirmed') Bevestigd @break
                    @case('delivered') Geleverd @break
                    @case('cancelled') Geannuleerd @break
                    @default {{ $order->status }}
                @endswitch
            </span>
        </p>
    </div>

    <div style="display: flex; justify-content: space-between; margin-bottom: 30px;">
        <div style="width: 48%;">
            <div class="detail-box">
                <h3>Verkoper</h3>
                <p><strong>{{ $order->vendor->name }}</strong></p>
                @if($order->vendor->contact_email)
                    <p>Email: {{ $order->vendor->contact_email }}</p>
                @endif
                @if($order->vendor->phone)
                    <p>Telefoon: {{ $order->vendor->phone }}</p>
                @endif
            </div>
        </div>
        
        <div style="width: 48%;">
            <div class="detail-box">
                <h3>Leverancier</h3>
                <p><strong>{{ $order->supplier->name }}</strong></p>
                @if($order->supplier->contact_email)
                    <p>Email: {{ $order->supplier->contact_email }}</p>
                @endif
                @if($order->supplier->phone)
                    <p>Telefoon: {{ $order->supplier->phone }}</p>
                @endif
            </div>
        </div>
    </div>

    <h2>Bestelde Items</h2>
    <table class="items-table">
        <thead>
            <tr>
                <th>Item</th>
                <th>Categorie</th>
                <th>Aantal</th>
                <th>Eenheid</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->orderItems as $orderItem)
                <tr>
                    <td>{{ $orderItem->item->name }}</td>
                    <td>{{ $orderItem->item->category->name ?? '-' }}</td>
                    <td>{{ $orderItem->quantity }}</td>
                    <td>{{ $orderItem->item->unit->name ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div style="margin-top: 30px;">
        <p><strong>Totaal aantal items:</strong> {{ $order->orderItems->sum('quantity') }}</p>
        <p><strong>Aantal verschillende producten:</strong> {{ $order->orderItems->count() }}</p>
    </div>

    <div class="footer">
        <p>Gegenereerd op {{ now()->format('d/m/Y H:i') }}</p>
        <p>Festival Management Systeem</p>
    </div>
</body>
</html>