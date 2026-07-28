<?php

return [
    // ─── KATEGORI PENGELUARAN ───
    'expense' => [
        // Yang bisa dipilih user di form (Biaya Real)
        'user' => [
            ['key' => 'Listrik'],
            ['key' => 'Air'],
            ['key' => 'Sewa'],
            ['key' => 'Gaji Karyawan'],
            ['key' => 'Transportasi'],
            ['key' => 'ATK / Perlengkapan'],
            ['key' => 'Maintenance / Service'],
            ['key' => 'Internet / Pulsa'],
            ['key' => 'Makan / Minum'],
            ['key' => 'Promosi / Iklan'],
            ['key' => 'Lain-lain'],
            ['key' => 'Amal'],
            ['key' => 'Jasa Cetak'],
            ['key' => 'Jasa Servis'],
            ['key' => 'Prive', 'pnl' => false, 'filter' => 'cash_movement'],
        ],
        // Dicatat otomatis oleh sistem
        'system' => [
            // pure cash movement: tidak masuk perhitungan laba-rugi
            ['key' => 'Stok Masuk',     'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Piutang',        'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Cash Keluar',    'pnl' => false, 'filter' => 'cash_movement'],
            // tetap masuk laba-rugi, tapi di tab Mutasi
            ['key' => 'Biaya MDR',      'pnl' => true,  'filter' => 'cash_movement'],
            ['key' => 'Biaya Admin Topup', 'pnl' => true, 'filter' => 'cash_movement'],
            ['key' => 'Stok Opname Minus', 'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Penyesuaian Kas',   'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Pending EDC',       'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'OMSET',             'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Retur Penjualan',   'pnl' => false, 'filter' => 'cash_movement'],
        ],
    ],

    // ─── KATEGORI PENDAPATAN ───
    'income' => [
        // Yang bisa dipilih user di form
        'user' => [
            ['key' => 'Pendapatan Lainnya'],
        ],
        // Dicatat otomatis oleh sistem
        'system' => [
            // pure cash movement: tidak masuk pendapatan operasional
            ['key' => 'Piutang',           'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Transfer Masuk',    'pnl' => false, 'filter' => 'cash_movement'],
            ['key' => 'Pending EDC',       'pnl' => false, 'filter' => 'cash_movement'],
            // pendapatan real: masuk laba-rugi & tab Pendapatan Real
            ['key' => 'Penjualan',         'pnl' => true,  'filter' => 'real'],
            ['key' => 'Jasa Servis',       'pnl' => true,  'filter' => 'real'],
            ['key' => 'Jasa Cetak',        'pnl' => true,  'filter' => 'real'],
            ['key' => 'Stok Opname Plus',  'pnl' => false, 'filter' => 'real'],
            ['key' => 'Penyesuaian Kas',   'pnl' => false, 'filter' => 'real'],
            ['key' => 'Retur Pembelian',   'pnl' => false, 'filter' => 'real'],
            ['key' => 'OMSET',             'pnl' => true,  'filter' => 'cash_movement'],
            ['key' => 'Jasa Tarik Tunai EDC', 'pnl' => true, 'filter' => 'real'],
        ],
    ],
];
