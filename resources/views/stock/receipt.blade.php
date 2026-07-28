<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resi {{ $receipt->receipt_id }}</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'Consolas', 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 12px 10px;
            color: #000;
            font-size: 15px;
            line-height: 1.5;
            font-weight: 700;
        }
        .header { text-align: center; margin-bottom: 12px; }
        .header h3 { margin: 0 0 4px; font-weight: 900; font-size: 22px; letter-spacing: 0.5px; }
        .header p { margin: 3px 0; font-size: 13px; color: #000; font-weight: 600; }
        .divider { border-top: 2px dashed #000; margin: 10px 0; }
        .info { font-size: 13px; margin-bottom: 10px; font-weight: 600; }
        .info span { display: block; margin: 2px 0; }
        .info strong { font-weight: 900; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { padding: 3px 0; text-align: left; font-weight: 600; }
        th { border-bottom: 2px solid #000; font-weight: 900; font-size: 13px; padding-bottom: 4px; }
        .qty { text-align: center; }
        .price { text-align: right; }
        .total-row td { font-weight: 900; border-top: 2px solid #000; padding-top: 6px; }
        .grand-total td { font-weight: 900; font-size: 16px; }
        .footer { text-align: center; margin-top: 12px; font-size: 13px; line-height: 1.6; }
        .footer p { margin: 3px 0; font-weight: 700; }
        .footer strong { font-weight: 900; }
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 4mm; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    @php $profile = \App\Models\StoreProfile::first(); @endphp
    <div class="header">
        <h3>{{ $profile->store_name ?? 'ADI CELL' }}</h3>
        <p>{{ $profile->address ?? 'Jl. Toko No. 123' }}</p>
        <p>Telp: {{ $profile->phone ?? '0812-3456-7890' }}</p>
        @if(!empty($profile->email))
        <p>{{ $profile->email }}</p>
        @endif
    </div>
    <div class="divider"></div>
    <div class="info">
        <span>No: <strong>{{ $receipt->receipt_id }}</strong></span>
        <span>Tgl: {{ \Carbon\Carbon::parse($receipt->date)->isoFormat('D MMM YYYY  HH:mm') }}</span>
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
            <tr>
                <td>{{ $item->product->name ?? '-' }}</td>
                <td class="qty">{{ $item->qty }}</td>
                <td class="price">{{ number_format($item->price, 0, ',', '.') }}</td>
                <td class="price">{{ number_format($item->qty * $item->price, 0, ',', '.') }}</td>
            </tr>
            @endforeach
            <tr class="total-row">
                <td colspan="3">Subtotal</td>
                <td class="price">{{ number_format($receipt->total, 0, ',', '.') }}</td>
            </tr>
            @if(($receipt->income->discount ?? 0) > 0)
            <tr>
                <td colspan="3">Diskon</td>
                <td class="price">-{{ number_format($receipt->income->discount, 0, ',', '.') }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td colspan="3">Total</td>
                <td class="price">{{ number_format($receipt->income->amount ?? $receipt->total, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td colspan="3">Tunai</td>
                <td class="price">{{ number_format($receipt->income->amount ?? $receipt->total, 0, ',', '.') }}</td>
            </tr>
            <tr class="grand-total">
                <td colspan="3">Kembali</td>
                <td class="price">{{ number_format(($receipt->income->amount ?? $receipt->total) - $receipt->total, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>
    <div class="divider"></div>
    <div class="footer">
        <p><strong>{{ $profile->footer_text ?? 'Terima kasih!' }}</strong></p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
    </div>
    <div class="no-print" style="text-align:center;margin-top:15px;">
        <a href="{{ route('stock.receipt.pdf', $receipt->receipt_id) }}" target="_blank" style="display:inline-block;padding:10px 28px;border:none;border-radius:8px;background:#3b82f6;color:#fff;font-weight:700;text-decoration:none;margin-bottom:8px;font-size:14px;">
            <i class="fas fa-file-pdf"></i> Cetak PDF
        </a>
        <br>
        <button onclick="window.print()" style="padding:10px 28px;border:none;border-radius:8px;background:#10b981;color:#fff;font-weight:700;cursor:pointer;font-size:14px;font-family:sans-serif;">
            <i class="fas fa-print"></i> Cetak Browser
        </button>
        <br><br>
        <a href="{{ route('stock.sales') }}" style="color:#64748b;font-size:12px;font-weight:600;font-family:sans-serif;">Kembali ke Penjualan</a>
    </div>
</body>
</html>
