<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('print_orders', function (Blueprint $table) {
            $table->string('print_calc_ref', 64)->nullable()->after('description');
            $table->index('print_calc_ref');
        });
    }

    public function down(): void
    {
        Schema::table('print_orders', function (Blueprint $table) {
            $table->dropIndex(['print_calc_ref']);
            $table->dropColumn('print_calc_ref');
        });
    }
};
