<?php

use App\Models\Account;
use App\Models\Mutation;
use App\Models\Receivable;
use App\Models\ReceivablePayment;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $cashAccount = Account::active()->where('name', config('accounts.cash_name'))->first();
        $piutangAccount = Account::where('type', 'receivable')->first();

        if (!$cashAccount || !$piutangAccount) return;

        // Hapus semua mutation piutang yang salah, lalu recreate
        Mutation::where('source', 'piutang')->delete();

        // Buat IN mutation untuk setiap receivable (Cash → Piutang)
        $receivables = Receivable::all();
        foreach ($receivables as $r) {
            Mutation::create([
                'from_account_id' => $cashAccount->id,
                'to_account_id' => $piutangAccount->id,
                'amount' => $r->amount,
                'date' => $r->date ?? now(),
                'description' => "Piutang {$r->name} (#{$r->id})",
                'source' => 'piutang',
            ]);
        }

        // Buat OUT mutation untuk setiap pembayaran (Piutang → Akun bayar)
        $payments = ReceivablePayment::with('receivable')->get();
        foreach ($payments as $p) {
            if (!$p->receivable) continue;

            Mutation::create([
                'from_account_id' => $piutangAccount->id,
                'to_account_id' => $p->account_id ?? $cashAccount->id,
                'amount' => $p->amount,
                'date' => $p->date ?? now(),
                'description' => "Bayar piutang {$p->receivable->name}",
                'source' => 'piutang',
            ]);
        }
    }

    public function down(): void
    {
        Mutation::where('source', 'piutang')->delete();
    }
};
