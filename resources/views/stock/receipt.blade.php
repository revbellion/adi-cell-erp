<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Resi {{ $receipt->receipt_id }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inconsolata:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Consolas', 'Courier New', monospace;
            width: 80mm;
            margin: 0 auto;
            padding: 10px;
            color: #000;
            font-size: 13px;
            line-height: 1.4;
            font-weight: 500;
            text-shadow: 0 0 0.3px #000;
        }
        .header { text-align: center; margin-bottom: 10px; }
        .header h3 { margin: 0; font-weight: 800; font-size: 18px; }
        .header p { margin: 2px 0; font-size: 11px; color: #222; font-weight: 500; }
        .divider { border-top: 1px dashed #333; margin: 8px 0; }
        .info { font-size: 11px; margin-bottom: 8px; font-weight: 500; }
        .info span { display: block; }
        table { width: 100%; border-collapse: collapse; font-size: 11px; }
        th, td { padding: 3px 0; text-align: left; font-weight: 500; }
        th { border-bottom: 1px solid #333; font-weight: 700; }
        .qty { text-align: center; }
        .price { text-align: right; }
        .total-row td { font-weight: 700; border-top: 1px solid #333; padding-top: 5px; }
        .grand-total td { font-weight: 800; font-size: 14px; }
        .footer { text-align: center; margin-top: 12px; font-size: 11px; }
        .footer p { margin: 2px 0; font-weight: 500; }
        @media print {
            @page { margin: 0; size: 80mm auto; }
            body { margin: 5mm; -webkit-print-color-adjust: exact; print-color-adjust: exact; -webkit-text-stroke: 0.3px; }
            .no-print { display: none !important; }
        }
    </style>
</head>
<body>
    <div class="header">
        <h3>ADI CELL</h3>
        <p>Jl. Toko No. 123</p>
        <p>Telp: 0812-3456-7890</p>
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
                <td colspan="3"><strong>Total</strong></td>
                <td class="price"><strong>{{ number_format($receipt->total, 0, ',', '.') }}</strong></td>
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
        <p><strong>Terima kasih!</strong></p>
        <p>Barang yang sudah dibeli tidak dapat dikembalikan</p>
    </div>
    <div class="no-print" style="text-align:center;margin-top:15px;">
        <a href="{{ route('stock.receipt.pdf', $receipt->receipt_id) }}" target="_blank" style="display:inline-block;padding:8px 24px;border:none;border-radius:8px;background:#3b82f6;color:#fff;font-weight:600;text-decoration:none;margin-bottom:8px;">
            <i class="fas fa-file-pdf"></i> Cetak PDF
        </a>
        <br>
        <button onclick="window.print()" style="padding:8px 24px;border:none;border-radius:8px;background:#10b981;color:#fff;font-weight:600;cursor:pointer;">
            <i class="fas fa-print"></i> Cetak Browser
        </button>
        <br><br>
        <a href="{{ route('stock.sales') }}" style="color:#64748b;font-size:11px;">Kembali ke Penjualan</a>
    </div>
</body>
</html>
