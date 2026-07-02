<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $permissions = json_decode($user->permissions, true) ?? [];

            if (in_array('receivables', $permissions) && !in_array('pending', $permissions)) {
                $permissions[] = 'pending';
                DB::table('users')
                    ->where('id', $user->id)
                    ->update(['permissions' => json_encode($permissions)]);
            }
        }
    }

    public function down(): void
    {
        $users = DB::table('users')->get();

        foreach ($users as $user) {
            $permissions = json_decode($user->permissions, true) ?? [];
            $permissions = array_values(array_filter($permissions, fn($p) => $p !== 'pending'));
            DB::table('users')
                ->where('id', $user->id)
                ->update(['permissions' => json_encode($permissions)]);
        }
    }
};
