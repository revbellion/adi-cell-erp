<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Cetak Resi Gabungan ({{ $orders->count() }} item)</title>
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
        .divider-solid { border-top: 2px solid #000; margin: 10px 0; }
        .info { font-size: 13px; margin-bottom: 10px; font-weight: 600; }
        .info span { display: block; margin: 2px 0; }
        .info strong { font-weight: 900; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        td { padding: 3px 0; font-weight: 600; }
        .right { text-align: right; }
        .col-header { font-weight: 900; border-bottom: 2px solid #000; padding-bottom: 4px; font-size: 13px; }
        .grand-total td { font-weight: 900; font-size: 16px; border-top: 2px solid #000; padding-top: 6px; }
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
    @php
        $profile = \App\Models\StoreProfile::first();
        $grandTotal = $orders->sum('total');
        $totalQty = $orders->sum('quantity');
    @endphp

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
        <span>No: <strong>{{ $orders->first()->receipt_id }} - {{ $orders->last()->receipt_id }}</strong></span>
        <span>Tgl: {{ $orders->first()->date->isoFormat('D MMM YYYY  HH:mm') }}</span>
        <span>Akun: {{ $orders->first()->account->name ?? '-' }}</span>
    </div>
    <div class="divider"></div>
    <table>
        <tr>
            <td class="col-header">Layanan</td>
            <td class="right col-header">Qty</td>
            <td class="right col-header">Subtotal</td>
        </tr>
        @foreach($orders as $order)
        <tr>
            <td>{{ $order->service_label }}</td>
            <td class="right">{{ number_format($order->quantity, 0, ',', '.') }} lbr</td>
            <td class="right">{{ number_format($order->total, 0, ',', '.') }}</td>
        </tr>
        @endforeach
        <tr>
            <td colspan="2" style="text-align:right;font-weight:900;padding-top:6px;">Total</td>
            <td class="right" style="font-weight:900;border-top:2px solid #000;padding-top:6px;">{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
        <tr class="grand-total">
            <td colspan="2">Bayar</td>
            <td class="right">{{ number_format($grandTotal, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td colspan="2">Kembali</td>
            <td class="right">Rp 0</td>
        </tr>
    </table>
    <div class="divider-solid"></div>
    <div class="footer">
        <p><strong>{{ $profile->footer_text ?? 'Terima kasih!' }}</strong></p>
        <p style="font-size:12px;">Jasa Cetak & Fotokopi</p>
    </div>

    <div class="no-print" style="text-align:center;margin-top:15px;">
        <button onclick="window.print()" style="padding:10px 28px;border:none;border-radius:8px;background:#10b981;color:#fff;font-weight:700;cursor:pointer;font-size:14px;font-family:sans-serif;">
            <i class="fas fa-print"></i> Cetak Resi ({{ $orders->count() }} item)
        </button>
        <br><br>
        <a href="{{ route('print-orders.index') }}" style="color:#64748b;font-size:12px;font-weight:600;font-family:sans-serif;">Kembali ke Jasa Cetak</a>
    </div>
</body>
</html>
