<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="utf-8">
    <title>Gecombineerde Bestellingen</title>
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
            text-align: left;
        }

        .items-table td {
            text-align: left;
        }

        .items-table td.number {
            text-align: right;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-confirmed { background-color: #d4edda; color: #155724; }

        .footer {
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ccc;
            margin-top: 40px;
            padding-top: 15px;
        }

        .total-row {
            font-weight: bold;
            background-color: #f5f5f5;
        }

        .grand-total {
            font-size: 14px;
            font-weight: bold;
            text-align: right;
            margin-top: 10px;
        }

        .order-separator {
            border-top: 2px solid #ddd;
            margin: 30px 0 20px 0;
            position: relative;
        }

        .order-separator:first-child {
            border-top: none;
            margin-top: 0;
        }

        .order-number-badge {
            background: #333;
            color: white;
            padding: 5px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
            margin-bottom: 15px;
        }

        .consolidated-summary {
            background: #e8f4f8;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #0066cc;
            margin-bottom: 30px;
        }

        .consolidated-summary h3 {
            color: #0066cc;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Gecombineerde Festival Bestellingen</h1>
        <p>Bestelnummers: 
            @foreach($orders as $index => $order)
                #{{ $order->id }}{{ !$loop->last ? ', ' : '' }}
            @endforeach
        </p>
    </div>

    <div class="section consolidated-summary">
        <h3>Overzicht Gecombineerde Bestelling</h3>
        <div style="display: flex; justify-content: space-between; gap: 20px;">
            <div>
                <p><strong>Leverancier:</strong> {{ $orders->first()->supplier->name }}</p>
                @if($orders->first()->supplier->contact_email)
                    <p><strong>Email:</strong> {{ $orders->first()->supplier->contact_email }}</p>
                @endif
                @if($orders->first()->supplier->contact_phone)
                    <p><strong>Telefoon:</strong> {{ $orders->first()->supplier->contact_phone }}</p>
                @endif
            </div>
            <div>
                <p><strong>Aantal bestellingen:</strong> {{ $orders->count() }}</p>
                <p><strong>Totaal aantal items:</strong> {{ $orders->sum(fn($order) => $order->orderItems->sum('quantity')) }}</p>
                <p><strong>Verschillende producten:</strong> {{ $orders->flatMap->orderItems->pluck('item_id')->unique()->count() }}</p>
            </div>
        </div>
    </div>

    @php
        $grandTotal = 0;
        $consolidatedItems = collect();
        
        // Consolideer alle items van alle orders
        foreach($orders as $order) {
            foreach($order->orderItems as $orderItem) {
                $costPrice = DB::table('supplier_item')
                    ->where('supplier_id', $order->supplier_id)
                    ->where('item_id', $orderItem->item_id)
                    ->value('cost_price');
                
                $itemKey = $orderItem->item_id;
                
                if ($consolidatedItems->has($itemKey)) {
                    // Item bestaat al, voeg quantity toe
                    $existing = $consolidatedItems->get($itemKey);
                    $existing['quantity'] += $orderItem->quantity;
                    $existing['total'] = $existing['quantity'] * $costPrice;
                    $existing['orders'][] = $order->id;
                    $consolidatedItems->put($itemKey, $existing);
                } else {
                    // Nieuw item
                    $consolidatedItems->put($itemKey, [
                        'item' => $orderItem->item,
                        'quantity' => $orderItem->quantity,
                        'cost_price' => $costPrice,
                        'total' => $costPrice * $orderItem->quantity,
                        'orders' => [$order->id]
                    ]);
                }
            }
        }
        
        $grandTotal = $consolidatedItems->sum('total');
    @endphp

    <div class="section">
        <h2>Gecombineerde Items Overzicht</h2>
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Categorie</th>
                    <th>Totaal Aantal</th>
                    <th>Eenheid</th>
                    <th class="number">Prijs per eenheid</th>
                    <th class="number">Totaal</th>
                    <th>Van bestellingen</th>
                </tr>
            </thead>
            <tbody>
                @foreach($consolidatedItems as $item)
                    <tr>
                        <td>{{ $item['item']->name }}</td>
                        <td>{{ $item['item']->category->name ?? '-' }}</td>
                        <td>{{ $item['quantity'] }}</td>
                        <td>{{ $item['item']->unit->name ?? '-' }}</td>
                        <td class="number">€ {{ number_format($item['cost_price'], 2, ',', '.') }}</td>
                        <td class="number">€ {{ number_format($item['total'], 2, ',', '.') }}</td>
                        <td style="font-size: 10px;">
                            @foreach(array_unique($item['orders']) as $orderId)
                                #{{ $orderId }}{{ !$loop->last ? ', ' : '' }}
                            @endforeach
                        </td>
                    </tr>
                @endforeach
                
                <tr class="total-row">
                    <td colspan="5" class="number">Totaal:</td>
                    <td class="number">€ {{ number_format($grandTotal, 2, ',', '.') }}</td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        
        <div class="grand-total">
            Totaalbedrag: € {{ number_format($grandTotal, 2, ',', '.') }}
        </div>
    </div>

    <!-- Individual Order Details -->
    <div class="section">
        <h2>Details per Bestelling</h2>
        
        @foreach($orders as $order)
            <div class="order-separator">
                <div class="order-number-badge">Bestelling #{{ $order->id }}</div>
                
                <div class="details-row">
                    <div class="detail-box">
                        <h3>Evenement & Stand</h3>
                        <p><strong>{{ $order->vendor->event->name }}</strong></p>
                        
                        <p>Stand: {{ $order->vendor->name }}</p>
                        <p>Besteldatum: {{ $order->ordered_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div class="detail-box">
                        <h3>Status</h3>
                        <p>
                            <span class="status-badge status-confirmed">Bevestigd</span>
                        </p>
                        <p><strong>Items:</strong> {{ $order->orderItems->sum('quantity') }}</p>
                        <p><strong>Producten:</strong> {{ $order->orderItems->count() }}</p>
                    </div>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Categorie</th>
                            <th>Aantal</th>
                            <th>Eenheid</th>
                            <th class="number">Prijs per eenheid</th>
                            <th class="number">Totaal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $orderTotal = 0; @endphp
                        @foreach($order->orderItems as $orderItem)
                            @php
                                $costPrice = DB::table('supplier_item')
                                    ->where('supplier_id', $order->supplier_id)
                                    ->where('item_id', $orderItem->item_id)
                                    ->value('cost_price');
                                
                                $lineTotal = $costPrice * $orderItem->quantity;
                                $orderTotal += $lineTotal;
                            @endphp
                            
                            <tr>
                                <td>{{ $orderItem->item->name }}</td>
                                <td>{{ $orderItem->item->category->name ?? '-' }}</td>
                                <td>{{ $orderItem->quantity }}</td>
                                <td>{{ $orderItem->item->unit->name ?? '-' }}</td>
                                <td class="number">€ {{ number_format($costPrice, 2, ',', '.') }}</td>
                                <td class="number">€ {{ number_format($lineTotal, 2, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        
                        <tr class="total-row">
                            <td colspan="5" class="number">Subtotaal bestelling #{{ $order->id }}:</td>
                            <td class="number">€ {{ number_format($orderTotal, 2, ',', '.') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    <div class="footer">
        <p>Gegenereerd op {{ now()->format('d/m/Y H:i') }}</p>
        <p>Festival Management Systeem - Gecombineerde Bestelling</p>
    </div>
</body>
</html>