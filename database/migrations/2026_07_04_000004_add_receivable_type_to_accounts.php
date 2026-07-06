<?php

use App\Models\Account;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE accounts MODIFY COLUMN type ENUM('cash','bank','ewallet','ppob','other','receivable') NOT NULL");

        Account::create([
            'name' => 'Piutang',
            'type' => 'receivable',
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        Account::where('type', 'receivable')->delete();
        DB::statement("ALTER TABLE accounts MODIFY COLUMN type ENUM('cash','bank','ewallet','ppob','other') NOT NULL");
    }
};
