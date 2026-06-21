<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->string('bank_type', 10)->nullable()->after('type');
            $table->decimal('mdr_rate', 5, 2)->default(0)->after('bank_type');
            $table->integer('mdr_amount')->default(0)->after('mdr_rate');
            $table->integer('net_amount')->default(0)->after('mdr_amount');
        });
    }

    public function down(): void
    {
        Schema::table('pending_transactions', function (Blueprint $table) {
            $table->dropColumn(['bank_type', 'mdr_rate', 'mdr_amount', 'net_amount']);
        });
    }
};
