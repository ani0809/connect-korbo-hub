<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Label</title>
    <style>
        body { font-family: monospace; margin: 0; padding: 16px; }
        .label { width: 78mm; border: 1px dashed #333; padding: 10px; line-height: 1.5; }
        .sep { border-top: 1px dashed #333; margin: 6px 0; }
        .barcode { font-size: 22px; letter-spacing: 2px; text-align: center; margin: 8px 0; }
        @media print {
            body { margin: 0; padding: 0; }
            .print-hide { display: none !important; }
            .label { border: none; width: 80mm; }
        }
    </style>
</head>
<body>
<button class="print-hide" onclick="window.print()">Print</button>
<div class="label">
    <strong>{{ setting('site_name', config('app.name')) }}</strong>
    <div class="sep"></div>
    <div>TO:</div>
    <div>{{ $shipment->order->shipping_name }}</div>
    <div>{{ $shipment->order->shipping_address }}</div>
    <div>{{ $shipment->order->shipping_city }} - {{ $shipment->order->shipping_postal_code }}</div>
    <div>📞 {{ $shipment->order->shipping_phone }}</div>
    <div class="sep"></div>
    <div>ORDER: {{ $shipment->order->order_number }}</div>
    <div>ITEMS: {{ $shipment->order->items()->sum('quantity') }} ({{ number_format((float) $shipment->weight, 2) }} kg)</div>
    <div>COD: ৳{{ number_format((float) $shipment->cod_amount, 2) }}</div>
    <div class="sep"></div>
    <div class="barcode">*{{ $shipment->tracking_code }}*</div>
    <div style="text-align:center">{{ $shipment->tracking_code }}</div>
    <div class="sep"></div>
    <div>FROM: {{ setting('shop_address', '-') }}</div>
    <div>📞 {{ setting('shop_phone', '-') }}</div>
</div>
</body>
</html>
