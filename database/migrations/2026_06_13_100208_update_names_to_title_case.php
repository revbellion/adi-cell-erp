<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // -- Accounts --
        $accountMap = [
            'SHOPEEPAY'     => 'ShopeePay',
            'DANA'          => 'Dana',
            'ORDERKUOTA'    => 'OrderKuota',
            'GOPAY'         => 'GoPay',
            'RITA'          => 'Rita',
            'SIDIVA'        => 'Sidiva',
            'SIMPEL'        => 'Simpel',
            'DIGIPOS'       => 'Digipos',
            'BCA'           => 'BCA',
            'CASH'          => 'Cash',
            'EDC Pending'   => 'EDC Pending',
        ];
        foreach ($accountMap as $old => $new) {
            DB::table('accounts')->where('name', $old)->update(['name' => $new]);
        }

        // -- Expense categories --
        DB::table('expenses')
            ->where('category', 'TAGIHAN BULANAN')
            ->update(['category' => 'Tagihan Bulanan']);

        // -- Income categories --
        DB::table('incomes')
            ->where('category', 'OMZET')
            ->update(['category' => 'Omzet']);
    }

    public function down(): void
    {
        $reverseAccountMap = [
            'ShopeePay'   => 'SHOPEEPAY',
            'Dana'        => 'DANA',
            'OrderKuota'  => 'ORDERKUOTA',
            'GoPay'       => 'GOPAY',
            'Rita'        => 'RITA',
            'Sidiva'      => 'SIDIVA',
            'Simpel'      => 'SIMPEL',
            'Digipos'     => 'DIGIPOS',
            'BCA'         => 'BCA',
            'Cash'        => 'CASH',
            'EDC Pending' => 'EDC Pending',
        ];
        foreach ($reverseAccountMap as $new => $old) {
            DB::table('accounts')->where('name', $new)->update(['name' => $old]);
        }

        DB::table('expenses')
            ->where('category', 'Tagihan Bulanan')
            ->update(['category' => 'TAGIHAN BULANAN']);

        DB::table('incomes')
            ->where('category', 'Omzet')
            ->update(['category' => 'OMZET']);
    }
};
