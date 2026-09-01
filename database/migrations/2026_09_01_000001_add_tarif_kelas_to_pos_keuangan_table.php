<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pos_keuangan', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_keuangan', 'tarif_per_kelas')) {
                $table->json('tarif_per_kelas')->nullable()->after('nominal_default');
            }
            if (!Schema::hasColumn('pos_keuangan', 'target_kelas')) {
                $table->json('target_kelas')->nullable()->after('tarif_per_kelas');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pos_keuangan', function (Blueprint $table) {
            if (Schema::hasColumn('pos_keuangan', 'target_kelas')) {
                $table->dropColumn('target_kelas');
            }
            if (Schema::hasColumn('pos_keuangan', 'tarif_per_kelas')) {
                $table->dropColumn('tarif_per_kelas');
            }
        });
    }
};
