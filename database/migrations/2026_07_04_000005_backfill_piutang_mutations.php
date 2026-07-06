<?php

use App\Models\Account;
use App\Models\Mutation;
use App\Models\Receivable;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $cashAccount = Account::active()->where('name', config('accounts.cash_name'))->first();
        $piutangAccount = Account::where('type', 'receivable')->first();

        if (!$cashAccount || !$piutangAccount) return;

        // Backfill mutation untuk piutang unpaid yang belum punya mutation
        $receivables = Receivable::where('status', 'unpaid')->with('receivablePayments')->get();

        foreach ($receivables as $r) {
            $sisa = $r->amount - $r->receivablePayments->sum('amount');
            if ($sisa <= 0) continue;

            // Cek apakah sudah ada mutation untuk receivable ini
            $exists = Mutation::where('source', 'piutang')
                ->where('description', 'like', "%{$r->name}%")
                ->where('amount', $sisa)
                ->exists();

            if (!$exists) {
                Mutation::create([
                    'from_account_id' => $cashAccount->id,
                    'to_account_id' => $piutangAccount->id,
                    'amount' => $sisa,
                    'date' => $r->date ?? now(),
                    'description' => "Piutang {$r->name}",
                    'source' => 'piutang',
                ]);
            }
        }

        // Backfill mutation untuk pembayaran piutang
        $payments = \App\Models\ReceivablePayment::with('receivable')->get();
        foreach ($payments as $p) {
            if (!$p->receivable) continue;

            $exists = Mutation::where('source', 'piutang')
                ->where('description', 'like', "Bayar piutang {$p->receivable->name}%")
                ->where('amount', $p->amount)
                ->exists();

            if (!$exists) {
                Mutation::create([
                    'from_account_id' => $piutangAccount->id,
                    'to_account_id' => $p->account_id ?? $cashAccount?->id,
                    'amount' => $p->amount,
                    'date' => $p->date ?? now(),
                    'description' => "Bayar piutang {$p->receivable->name}",
                    'source' => 'piutang',
                ]);
            }
        }
    }

    public function down(): void
    {
        Mutation::where('source', 'piutang')->delete();
    }
};
