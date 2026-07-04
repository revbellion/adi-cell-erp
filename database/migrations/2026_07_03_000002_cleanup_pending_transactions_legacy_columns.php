<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $bcaId = DB::table('accounts')->where('name', config('accounts.bca_name'))->value('id');
        $cashId = DB::table('accounts')->where('name', config('accounts.cash_name'))->value('id');
        $transitId = DB::table('accounts')->where('name', config('accounts.in_transit_name'))->value('id');

        // Convert pending Transfer: old Income on Transit → Mutation BCA → Transit
        $pendingTransferIds = DB::table('pending_transactions')
            ->where('status', 'pending')
            ->where('type', 'transfer')
            ->whereNotNull('income_id')
            ->pluck('id');

        foreach ($pendingTransferIds as $id) {
            $pending = DB::table('pending_transactions')->find($id);
            $income = DB::table('incomes')->find($pending->income_id);
            if ($income) {
                $mutationId = DB::table('mutations')->insertGetId([
                    'date' => $income->date,
                    'from_account_id' => $bcaId,
                    'to_account_id' => $transitId,
                    'amount' => $income->amount,
                    'description' => "Transfer pending: {$pending->description}",
                    'source' => 'pending',
                    'created_at' => $pending->created_at,
                    'updated_at' => $pending->updated_at,
                ]);
                DB::table('pending_transactions')->where('id', $id)->update(['mutation_id' => $mutationId]);
                DB::table('incomes')->where('id', $pending->income_id)->delete();
            }
        }

        // Convert pending EDC: old Expense on Transit → Mutation Cash → Transit
        $pendingEdcIds = DB::table('pending_transactions')
            ->where('status', 'pending')
            ->where('type', 'edc')
            ->whereNotNull('expense_id')
            ->pluck('id');

        foreach ($pendingEdcIds as $id) {
            $pending = DB::table('pending_transactions')->find($id);
            $expense = DB::table('expenses')->find($pending->expense_id);
            if ($expense) {
                $mutationId = DB::table('mutations')->insertGetId([
                    'date' => $expense->date,
                    'from_account_id' => $cashId,
                    'to_account_id' => $transitId,
                    'amount' => $expense->amount,
                    'description' => "EDC pending: {$pending->description}",
                    'source' => 'pending',
                    'created_at' => $pending->created_at,
                    'updated_at' => $pending->updated_at,
                ]);
                DB::table('pending_transactions')->where('id', $id)->update(['mutation_id' => $mutationId]);
                DB::table('expenses')->where('id', $pending->expense_id)->delete();
            }
        }

        // Drop legacy columns
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->dropColumn(['income_id', 'expense_id']);
        });
    }

    public function down(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('income_id')->nullable()->after('completed_account_id');
            $table->unsignedBigInteger('expense_id')->nullable()->after('income_id');
        });
    }
};
