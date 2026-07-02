<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $transitAccount = DB::table('accounts')
            ->where('name', config('accounts.in_transit_name', 'Dalam Perjalanan'))
            ->first();

        $bcaAccount = DB::table('accounts')
            ->where('name', config('accounts.bca_name', 'BCA'))
            ->first();

        if (!$transitAccount || !$bcaAccount) {
            return;
        }

        // Move transfer pending incomes from transit back to BCA
        DB::table('incomes')
            ->join('pending_transactions', 'pending_transactions.income_id', '=', 'incomes.id')
            ->where('pending_transactions.status', 'pending')
            ->where('pending_transactions.type', 'transfer')
            ->where('incomes.account_id', $transitAccount->id)
            ->update(['incomes.account_id' => $bcaAccount->id]);
    }

    public function down(): void
    {
        $transitAccount = DB::table('accounts')
            ->where('name', config('accounts.in_transit_name', 'Dalam Perjalanan'))
            ->first();

        $bcaAccount = DB::table('accounts')
            ->where('name', config('accounts.bca_name', 'BCA'))
            ->first();

        if (!$transitAccount || !$bcaAccount) {
            return;
        }

        // Move transfer pending incomes from BCA back to transit
        DB::table('incomes')
            ->join('pending_transactions', 'pending_transactions.income_id', '=', 'incomes.id')
            ->where('pending_transactions.status', 'pending')
            ->where('pending_transactions.type', 'transfer')
            ->where('incomes.account_id', $bcaAccount->id)
            ->update(['incomes.account_id' => $transitAccount->id]);
    }
};
