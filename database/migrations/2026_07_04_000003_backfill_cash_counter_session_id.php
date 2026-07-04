<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $sessions = DB::table('cash_counter_sessions')->get(['id', 'title']);

        foreach ($sessions as $session) {
            $pattern = 'Penyesuaian kas #' . $session->id . '%';

            DB::table('incomes')
                ->where('category', 'OMSET')
                ->where('description', 'like', $pattern)
                ->update(['cash_counter_session_id' => $session->id]);

            DB::table('expenses')
                ->where('category', 'OMSET')
                ->where('description', 'like', $pattern)
                ->update(['cash_counter_session_id' => $session->id]);
        }
    }

    public function down(): void
    {
        DB::table('incomes')->update(['cash_counter_session_id' => null]);
        DB::table('expenses')->update(['cash_counter_session_id' => null]);
    }
};
