<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Resi {{ $order->receipt_id }}</title>
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
        td { padding: 3px 0; text-align: left; font-weight: bold; }
        .right { text-align: right; }
        .col-header { font-weight: 900; border-bottom: 2px solid #000; padding-bottom: 3px; }
        .grand-total td { font-size: 15px; font-weight: 900; padding-top: 5px; border-top: 2px solid #000; }
        .footer { text-align: center; margin-top: 10px; font-size: 12px; font-weight: bold; line-height: 1.5; }
        .footer p { margin: 2px 0; }
        .desc { font-size: 10px; font-weight: normal; color: #333; margin-top: 4px; }
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
        <div>No: <strong>{{ $order->receipt_id }}</strong></div>
        <div>Tgl: {{ $order->date->isoFormat('D MMM YYYY  HH:mm') }}</div>
        <div>Akun: {{ $order->account->name ?? '-' }}</div>
    </div>
    <div class="divider"></div>
    <table>
        <tr>
            <td class="col-header">{{ $order->service_label }}</td>
            <td class="right col-header">Subtotal</td>
        </tr>
        <tr>
            <td>{{ number_format($order->quantity, 0, ',', '.') }} lbr</td>
            <td class="right">{{ number_format($order->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>@ Rp {{ number_format($order->price_per_unit, 0, ',', '.') }}/lbr</td>
            <td class="right"></td>
        </tr>
        <tr>
            <td style="text-align:right;padding-top:4px;"><strong>Total</strong></td>
            <td class="right" style="padding-top:4px;"><strong>{{ number_format($order->total, 0, ',', '.') }}</strong></td>
        </tr>
        <tr class="grand-total">
            <td>Bayar</td>
            <td class="right">{{ number_format($order->total, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Kembali</td>
            <td class="right">Rp 0</td>
        </tr>
    </table>
    @if($order->description)
    <div class="desc">{{ $order->description }}</div>
    @endif
    <div class="divider-solid"></div>
    <div class="footer">
        <p>{{ $profile->footer_text ?? 'Terima kasih!' }}</p>
        <p style="font-weight:normal;font-size:10px;">Jasa Cetak & Fotokopi</p>
    </div>

    <script type="text/javascript">
        window.onload = function() { window.print(); }
    </script>
</body>
</html>
