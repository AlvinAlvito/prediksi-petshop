<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_flrg_items', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('interval_set_id')->index();

            $t->string('current_state', 10); // A1..A5
            $t->string('next_state', 10);    // A1..A5
            $t->integer('freq')->default(0);

            $t->timestamps();

            $t->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
            $t->unique(['interval_set_id','current_state','next_state'], 'fts_flrg_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_flrg_items');
    }
};
