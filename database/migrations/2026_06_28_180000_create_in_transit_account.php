<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('accounts')->insert([
            'name' => config('accounts.in_transit_name', 'Dalam Perjalanan'),
            'type' => 'other',
            'is_active' => true,
        ]);
    }

    public function down(): void
    {
        DB::table('accounts')->where('name', config('accounts.in_transit_name', 'Dalam Perjalanan'))->delete();
    }
};
