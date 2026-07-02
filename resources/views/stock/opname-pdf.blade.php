<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Stok Opname</title>
    <style>
        @page { margin: 10mm; }
        body {
            font-family: 'DejaVu Sans Mono', monospace;
            font-size: 9px;
            color: #000;
        }
        .header { text-align: center; margin-bottom: 12px; }
        .header h1 { margin: 0; font-size: 16px; font-weight: bold; }
        .header p { margin: 2px 0; font-size: 10px; }
        .divider { border-top: 1px solid #000; margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; font-size: 8px; }
        th {
            background: #f0f0f0;
            padding: 5px 4px;
            text-align: left;
            font-weight: bold;
            border: 1px solid #000;
            font-size: 8px;
        }
        td {
            padding: 3px 4px;
            border: 1px solid #000;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .no { width: 30px; text-align: center; }
        .col-no { width: 5%; }
        .col-name { width: 30%; }
        .col-category { width: 15%; }
        .col-stock { width: 10%; }
        .col-unit { width: 8%; }
        .col-price { width: 15%; }
        .col-physical { width: 12%; }
        .col-note { width: 15%; }
        .footer { margin-top: 20px; font-size: 9px; }
        .footer .sign-row { margin-top: 40px; }
        .footer .sign-row table { border: none; }
        .footer .sign-row td { border: none; text-align: center; padding-top: 30px; }
        .summary { margin-top: 8px; font-size: 9px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>ADI CELL</h1>
        <p>FORM STOK OPNAME</p>
        <p>Tanggal: {{ now()->isoFormat('D MMMM YYYY') }}</p>
    </div>
    <div class="divider"></div>

    <table>
        <thead>
            <tr>
                <th class="col-no">No</th>
                <th class="col-name">Barang</th>
                <th class="col-category">Kategori</th>
                <th class="col-stock text-center">Stok Sistem</th>
                <th class="col-unit text-center">Satuan</th>
                <th class="col-price text-right">Harga Beli</th>
                <th class="col-physical text-center">Stok Fisik</th>
                <th class="col-note">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($products as $i => $product)
            <tr>
                <td class="no">{{ $i + 1 }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->category->name ?? '-' }}</td>
                <td class="text-center">{{ $product->stock }}</td>
                <td class="text-center">{{ $product->unit }}</td>
                <td class="text-right">{{ number_format($product->purchase_price, 0, ',', '.') }}</td>
                <td></td>
                <td></td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        Total barang: {{ $products->count() }}
    </div>

    <div class="footer">
        <table class="sign-row">
            <tr>
                <td>Mengetahui,</td>
                <td>Petugas,</td>
            </tr>
            <tr>
                <td>(...................)</td>
                <td>(...................)</td>
            </tr>
        </table>
    </div>
</body>
</html>