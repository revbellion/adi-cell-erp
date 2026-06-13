<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE incomes MODIFY date DATETIME NOT NULL');
        DB::statement('ALTER TABLE expenses MODIFY date DATETIME NOT NULL');
        DB::statement('ALTER TABLE mutations MODIFY date DATETIME NOT NULL');
        DB::statement('ALTER TABLE receivables MODIFY date DATETIME NOT NULL');
        DB::statement('ALTER TABLE receivables MODIFY due_date DATETIME NOT NULL');
        DB::statement('ALTER TABLE receivable_payments MODIFY date DATETIME NOT NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE incomes MODIFY date DATE NOT NULL');
        DB::statement('ALTER TABLE expenses MODIFY date DATE NOT NULL');
        DB::statement('ALTER TABLE mutations MODIFY date DATE NOT NULL');
        DB::statement('ALTER TABLE receivables MODIFY date DATE NOT NULL');
        DB::statement('ALTER TABLE receivables MODIFY due_date DATE NOT NULL');
        DB::statement('ALTER TABLE receivable_payments MODIFY date DATE NOT NULL');
    }
};
