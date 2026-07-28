<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi {{ $receipt->receipt_id }}</title>
    <style>
        @page { margin: 6mm 5mm; }
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Courier', monospace;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
            width: 72mm;
            margin: 0 auto;
            font-weight: bold;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h1 {
            margin: 0 0 2px;
            font-size: 20px;
            font-weight: 900;
        }
        .header p { margin: 2px 0; font-size: 11px; font-weight: bold; }
        .divider { border-top: 2px dashed #000; margin: 8px 0; }
        .divider-solid { border-top: 2px solid #000; margin: 8px 0; }
        .info { font-size: 11px; margin-bottom: 8px; font-weight: bold; }
        .info div { margin: 2px 0; }
        .info strong { font-weight: 900; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { padding: 3px 0; text-align: left; font-weight: bold; }
        th {
            border-bottom: 2px solid #000;
            font-size: 11px;
            font-weight: 900;
            padding-bottom: 3px;
        }
        .qty { text-align: center; }
        .price { text-align: right; }
        .total-label { text-align: right; padding-right: 4px; }
        .total-value { text-align: right; font-weight: bold; }
        .grand-total td { font-size: 15px; font-weight: 900; padding-top: 4px; }
        .footer { text-align: center; margin-top: 10px; font-size: 12px; font-weight: bold; }
        .footer p { margin: 2px 0; }
    </style>
</head>
<body>
    @php $profile = \App\Models\StoreProfile::first(); @endphp
    <div class="header">
        <h1>{{ $profile->store_name ?? 'ADI CELL' }}</h1>
        <p>{{ $profile->address ?? 'Jl. Toko No. 123' }}</p>
        <p>Telp: {{ $profile->phone ?? '0812-3456-7890' }}</p>
        @if(!empty($profile->email))
        <p>{{ $profile->email }}</p>
        @endif
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
            <td style="width:60%;">Subtotal</td>
            <td class="total-value">{{ number_format($receipt->total, 0, ',', '.') }}</td>
        </tr>
        @if(($receipt->income->discount ?? 0) > 0)
        <tr>
            <td>Diskon</td>
            <td class="total-value">-{{ number_format($receipt->income->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr>
            <td style="width:60%;"><strong>Total</strong></td>
            <td class="total-value"><strong>{{ number_format($receipt->income->amount ?? $receipt->total, 0, ',', '.') }}</strong></td>
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
        <p>{{ $profile->footer_text ?? 'Terima kasih!' }}</p>
        <p style="font-weight:normal;font-size:10px;">Barang yang sudah dibeli tidak dapat dikembalikan</p>
    </div>

    <script type="text/javascript">
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
