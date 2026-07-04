<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('incomes')
            ->where('category', 'OMZET')
            ->update(['category' => 'OMSET']);

        DB::table('incomes')
            ->where('category', 'Omzet')
            ->update(['category' => 'OMSET']);
    }

    public function down(): void
    {
        DB::table('incomes')
            ->where('category', 'OMSET')
            ->update(['category' => 'OMZET']);
    }
};
