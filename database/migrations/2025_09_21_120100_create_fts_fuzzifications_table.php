<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_fuzzifications', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('universe_id')->index();
            $t->unsignedBigInteger('interval_set_id')->index();

            $t->string('produk')->index();
            $t->integer('urut');                  // 1..12
            $t->string('periode_label');          // April 2024, dst
            $t->integer('nilai');                 // jumlah
            $t->string('interval_kode', 10);      // u1..u5
            $t->string('fuzzy_kode', 10);         // A1..A5

            $t->timestamps();

            $t->foreign('universe_id')->references('id')->on('fts_universes')->cascadeOnDelete();
            $t->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
            $t->unique(['interval_set_id','urut'], 'fts_fuzzifications_iset_urut_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_fuzzifications');
    }
};
