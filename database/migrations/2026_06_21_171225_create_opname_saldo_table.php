<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opname_saldo', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('account_id')->nullable()->constrained('accounts')->nullOnDelete();
            $table->integer('opening_balance')->default(0);
            $table->integer('closing_balance')->default(0);
            $table->integer('difference')->default(0);
            $table->date('opname_date');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opname_saldo');
    }
};
