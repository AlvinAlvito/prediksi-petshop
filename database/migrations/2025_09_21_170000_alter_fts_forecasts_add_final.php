<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('fts_forecasts', function (Blueprint $t) {
            if (!Schema::hasColumn('fts_forecasts','f_final')) {
                $t->decimal('f_final', 10, 2)->nullable()->after('dt');
            }
            if (!Schema::hasColumn('fts_forecasts','f_final_round')) {
                $t->integer('f_final_round')->nullable()->after('f_final');
            }
        });
    }
    public function down(): void {
        Schema::table('fts_forecasts', function (Blueprint $t) {
            if (Schema::hasColumn('fts_forecasts','f_final_round')) $t->dropColumn('f_final_round');
            if (Schema::hasColumn('fts_forecasts','f_final')) $t->dropColumn('f_final');
        });
    }
};
