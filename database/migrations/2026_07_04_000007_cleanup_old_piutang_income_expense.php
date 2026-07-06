<?php

use App\Models\Expense;
use App\Models\Income;
use App\Models\Receivable;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // Set null FK di receivable agar tidak broken
        Receivable::whereNotNull('expense_id')->update(['expense_id' => null]);
        Receivable::whereNotNull('income_id')->update(['income_id' => null]);

        // Hapus Income & Expense kategori Piutang
        Income::where('category', 'Piutang')->delete();
        Expense::where('category', 'Piutang')->delete();
    }

    public function down(): void
    {
        // Tidak bisa restore — data sudah dihapus
    }
};
