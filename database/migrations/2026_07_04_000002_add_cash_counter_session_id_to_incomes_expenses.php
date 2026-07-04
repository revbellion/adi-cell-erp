<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('cash_counter_session_id')
                ->nullable()
                ->after('receivable_id')
                ->constrained('cash_counter_sessions')
                ->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('cash_counter_session_id')
                ->nullable()
                ->after('receivable_id')
                ->constrained('cash_counter_sessions')
                ->nullOnDelete();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->index('cash_counter_session_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index('cash_counter_session_id');
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex(['cash_counter_session_id']);
            $table->dropForeign(['cash_counter_session_id']);
            $table->dropColumn('cash_counter_session_id');
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->dropIndex(['cash_counter_session_id']);
            $table->dropForeign(['cash_counter_session_id']);
            $table->dropColumn('cash_counter_session_id');
        });
    }
};
