<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('fts_forecasts', function (Blueprint $t) {
            if (!Schema::hasColumn('fts_forecasts','ape')) {
                $t->decimal('ape', 10, 5)->nullable()->after('f_final_round');
            }
        });
    }
    public function down(): void {
        Schema::table('fts_forecasts', function (Blueprint $t) {
            if (Schema::hasColumn('fts_forecasts','ape')) $t->dropColumn('ape');
        });
    }
};
