<?php

use App\Models\Mutation;
use App\Models\Receivable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->foreignId('receivable_id')
                ->nullable()
                ->after('to_account_id')
                ->constrained('receivables')
                ->cascadeOnDelete();
        });

        // Backfill receivable_id dari description pattern: "Piutang Nama (#123)"
        $mutations = Mutation::where('source', 'piutang')->get();
        foreach ($mutations as $m) {
            if (preg_match('/#(\d+)/', $m->description, $match)) {
                $id = (int) $match[1];
                if (Receivable::where('id', $id)->exists()) {
                    Mutation::where('id', $m->id)->update(['receivable_id' => $id]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('mutations', function (Blueprint $table) {
            $table->dropForeign(['receivable_id']);
            $table->dropColumn('receivable_id');
        });
    }
};
