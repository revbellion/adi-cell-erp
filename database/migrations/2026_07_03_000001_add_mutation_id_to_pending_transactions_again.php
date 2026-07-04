<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->foreignId('mutation_id')->nullable()->after('expense_id')->constrained('mutations')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->dropForeign(['mutation_id']);
            $table->dropColumn('mutation_id');
        });
    }
};
