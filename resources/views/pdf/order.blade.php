<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Bestelling #{{ $order->id }}</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            font-size: 12px;
            line-height: 1.5;
            margin: 0;
            padding: 30px;
            color: #333;
            background-color: #fff;
        }

        h1, h2, h3 {
            margin: 0;
            padding: 0;
        }

        h1 {
            font-size: 22px;
            margin-bottom: 5px;
        }

        h2 {
            font-size: 16px;
            margin-bottom: 10px;
        }

        h3 {
            font-size: 13px;
            margin-bottom: 5px;
        }

        p {
            margin: 3px 0;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #222;
            padding-bottom: 15px;
            margin-bottom: 25px;
        }

        .section {
            margin-bottom: 25px;
        }

        .order-meta {
            background: #f1f1f1;
            padding: 10px 15px;
            border-radius: 5px;
        }

        .details-row {
            display: flex;
            justify-content: space-between;
            gap: 20px;
        }

        .detail-box {
            flex: 1;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 12px;
            background-color: #fafafa;
            margin-bottom: 10px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 8px 10px;
            font-size: 11px;
        }

        .items-table th {
            background-color: #f5f5f5;
            font-weight: bold;
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
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            margin-top: 40px;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Festival Bestelling</h1>
        <p>Bestelnummer: #{{ $order->id }}</p>
    </div>

    <div class="section order-meta">
        <h2>Bestelgegevens</h2>
        <p><strong>Besteldatum:</strong> {{ $order->ordered_at->format('d/m/Y H:i') }}</p>
        <p><strong>Status:</strong></p>
        <p>
            <span class="status-badge status-{{ $order->status }}">
                @switch($order->status)
                    @case('pending') In afwachting @break
                    @case('confirmed') Bevestigd @break
                    @case('delivered') Geleverd @break
                    @case('cancelled') Geannuleerd @break
                    @default {{ ucfirst($order->status) }}
                @endswitch
            </span>
        </p>

    </div>

    <div class="section details-row">
        <div class="detail-box">
            <h3>Evenement</h3>
            <p><strong>{{ $order->vendor->event->name }}</strong></p>
            <p>Stand: {{ $order->vendor->name }}</p>
            <p>Besteld door: {{ Auth::user()->name }}</p>
            <p>Email: {{ Auth::user()->email }}</p>
        </div>
        <div class="detail-box">
            <h3>Leverancier</h3>
            <p><strong>{{ $order->supplier->name }}</strong></p>
            @if($order->supplier->contact_email)
                <p>Email: {{ $order->supplier->contact_email }}</p>
            @endif
            @if($order->supplier->contact_phone)
                <p>Telefoon: {{ $order->supplier->contact_phone }}</p>
            @endif
        </div>
    </div>

    <div class="section">
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
    </div>

    <div class="section">
        <p><strong>Totaal aantal items:</strong> {{ $order->orderItems->sum('quantity') }}</p>
        <p><strong>Aantal verschillende producten:</strong> {{ $order->orderItems->count() }}</p>
    </div>

    <div class="footer">
        <p>Gegenereerd op {{ now()->format('d/m/Y H:i') }}</p>
        <p>Festival Management Systeem</p>
    </div>
</body>
</html>
