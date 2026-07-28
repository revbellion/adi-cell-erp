<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incomes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_counter_session_id');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cash_counter_session_id');
        });

        Schema::dropIfExists('cash_counter_sessions');
    }

    public function down(): void
    {
        Schema::create('cash_counter_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('opening_balance')->default(0);
            $table->string('title');
            $table->json('denominations');
            $table->integer('total_amount')->default(0);
            $table->timestamps();
        });

        Schema::table('incomes', function (Blueprint $table) {
            $table->foreignId('cash_counter_session_id')
                ->nullable()
                ->constrained('cash_counter_sessions')
                ->nullOnDelete();
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->foreignId('cash_counter_session_id')
                ->nullable()
                ->constrained('cash_counter_sessions')
                ->nullOnDelete();
        });
    }
};
