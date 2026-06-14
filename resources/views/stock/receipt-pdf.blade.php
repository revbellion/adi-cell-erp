<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi {{ $receipt->receipt_id }}</title>
    <style>
        @page { margin: 8mm 6mm; }
        body {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 10px;
            line-height: 1.3;
            color: #000;
            width: 70mm;
            margin: 0 auto;
        }
        .header { text-align: center; margin-bottom: 8px; }
        .header h1 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }
        .header p { margin: 1px 0; font-size: 9px; }
        .divider { border-top: 1px dashed #000; margin: 6px 0; }
        .divider-solid { border-top: 1px solid #000; margin: 6px 0; }
        .info { font-size: 9px; margin-bottom: 6px; }
        .info div { margin: 1px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 10px; }
        th, td { padding: 2px 0; text-align: left; font-weight: bold; }
        th {
            border-bottom: 1px solid #000;
            font-size: 10px;
        }
        .qty { text-align: center; }
        .price { text-align: right; }
        .item-row td { padding-top: 3px; }
        .total-label { text-align: right; padding-right: 4px; }
        .total-value { text-align: right; font-weight: bold; }
        .grand-total td { font-size: 14px; font-weight: bold; padding-top: 4px; }
        .footer { text-align: center; margin-top: 10px; font-size: 10px; font-weight: bold; }
        .footer p { margin: 2px 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ADI CELL</h1>
        <p>Jl. Toko No. 123</p>
        <p>Telp: 0812-3456-7890</p>
    </div>
    <div class="divider-solid"></div>
    <div class="info">
        <div>No: <strong>{{ $receipt->receipt_id }}</strong></div>
        <div>Tgl: {{ \Carbon\Carbon::parse($receipt->date)->isoFormat('D MMM YYYY  HH:mm') }}</div>
    </div>
    <div class="divider"></div>
    <table>
        <thead>
            <tr>
                <th>Barang</th>
                <th class="qty">Qty</th>
                <th class="price">Harga</th>
                <th class="price">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($receipt->items as $item)
            <tr class="item-row">
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="qty">{{ $item->qty }}</td>
                <td class="price">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="price">{{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    <div class="divider"></div>
    <table>
        <tr>
            <td style="width:60%;">Total</td>
            <td class="total-value">{{ number_format($receipt->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Tunai</td>
            <td class="total-value">{{ number_format($receipt->income->amount ?? $receipt->total, 0, ',', '.') }}</td>
        </tr>
        <tr class="grand-total">
            <td>Kembali</td>
            <td class="total-value">{{ number_format(($receipt->income->amount ?? $receipt->total) - $receipt->total, 0, ',', '.') }}</td>
        </tr>
    </table>
    <div class="divider-solid"></div>
    <div class="footer">
        <p>Terima kasih!</p>
        <p style="font-weight:normal;font-size:8px;">Barang yang sudah dibeli tidak dapat dikembalikan</p>
    </div>

    <script type="text/javascript">
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
