<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bill_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recurring_bill_id')->constrained('recurring_bills')->cascadeOnDelete();
            $table->string('period', 7);
            $table->decimal('amount', 15, 2);
            $table->timestamp('paid_at')->nullable();
            $table->foreignId('expense_id')->nullable()->constrained('expenses')->nullOnDelete();
            $table->timestamps();

            $table->unique(['recurring_bill_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bill_payments');
    }
};
