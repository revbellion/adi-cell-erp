<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['mutation_id']);
            $table->foreign('mutation_id')
                ->references('id')
                ->on('mutations')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            $table->dropForeign(['mutation_id']);
            $table->foreign('mutation_id')
                ->references('id')
                ->on('mutations')
                ->nullOnDelete();
        });
    }
};
