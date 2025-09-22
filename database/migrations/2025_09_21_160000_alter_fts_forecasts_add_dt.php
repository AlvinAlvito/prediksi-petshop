<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('fts_forecasts', function (Blueprint $t) {
            if (!Schema::hasColumn('fts_forecasts','curr_state')) {
                $t->string('curr_state', 10)->nullable()->after('prev_state'); // = prev_state
            }
            if (!Schema::hasColumn('fts_forecasts','next_state')) {
                $t->string('next_state', 10)->nullable()->after('curr_state');
            }
            if (!Schema::hasColumn('fts_forecasts','dt')) {
                $t->decimal('dt', 10, 2)->nullable()->after('f_value');
            }
        });
    }
    public function down(): void {
        Schema::table('fts_forecasts', function (Blueprint $t) {
            if (Schema::hasColumn('fts_forecasts','dt')) $t->dropColumn('dt');
            if (Schema::hasColumn('fts_forecasts','next_state')) $t->dropColumn('next_state');
            if (Schema::hasColumn('fts_forecasts','curr_state')) $t->dropColumn('curr_state');
        });
    }
};
