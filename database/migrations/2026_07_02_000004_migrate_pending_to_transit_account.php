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

        if (!$transitAccount) {
            return;
        }

        $cashAccount = DB::table('accounts')
            ->where('name', config('accounts.cash_name', 'Cash'))
            ->first();

        $bcaAccount = DB::table('accounts')
            ->where('name', config('accounts.bca_name', 'BCA'))
            ->first();

        if ($cashAccount) {
            DB::table('expenses')
                ->join('pending_transactions', 'pending_transactions.expense_id', '=', 'expenses.id')
                ->where('pending_transactions.status', 'pending')
                ->where('pending_transactions.type', 'edc')
                ->where('expenses.account_id', $cashAccount->id)
                ->update(['expenses.account_id' => $transitAccount->id]);
        }

        if ($bcaAccount) {
            DB::table('incomes')
                ->join('pending_transactions', 'pending_transactions.income_id', '=', 'incomes.id')
                ->where('pending_transactions.status', 'pending')
                ->where('pending_transactions.type', 'transfer')
                ->where('incomes.account_id', $bcaAccount->id)
                ->update(['incomes.account_id' => $transitAccount->id]);
        }
    }

    public function down(): void
    {
        $transitAccount = DB::table('accounts')
            ->where('name', config('accounts.in_transit_name', 'Dalam Perjalanan'))
            ->first();

        if (!$transitAccount) {
            return;
        }

        $cashAccount = DB::table('accounts')
            ->where('name', config('accounts.cash_name', 'Cash'))
            ->first();

        $bcaAccount = DB::table('accounts')
            ->where('name', config('accounts.bca_name', 'BCA'))
            ->first();

        if ($cashAccount) {
            DB::table('expenses')
                ->join('pending_transactions', 'pending_transactions.expense_id', '=', 'expenses.id')
                ->where('pending_transactions.status', 'pending')
                ->where('pending_transactions.type', 'edc')
                ->where('expenses.account_id', $transitAccount->id)
                ->update(['expenses.account_id' => $cashAccount->id]);
        }

        if ($bcaAccount) {
            DB::table('incomes')
                ->join('pending_transactions', 'pending_transactions.income_id', '=', 'incomes.id')
                ->where('pending_transactions.status', 'pending')
                ->where('pending_transactions.type', 'transfer')
                ->where('incomes.account_id', $transitAccount->id)
                ->update(['incomes.account_id' => $bcaAccount->id]);
        }
    }
};
