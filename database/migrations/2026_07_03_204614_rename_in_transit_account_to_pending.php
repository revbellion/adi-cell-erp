<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('accounts')
            ->where('name', 'Dalam Perjalanan')
            ->update(['name' => 'Pending']);
    }

    public function down(): void
    {
        DB::table('accounts')
            ->where('name', 'Pending')
            ->update(['name' => 'Dalam Perjalanan']);
    }
};
