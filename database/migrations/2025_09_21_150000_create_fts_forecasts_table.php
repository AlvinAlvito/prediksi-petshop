<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_forecasts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('universe_id')->index();
            $t->unsignedBigInteger('interval_set_id')->index();
            $t->unsignedBigInteger('matrix_id')->index();

            $t->string('produk')->index();
            $t->integer('urut');                 // 1..N
            $t->string('periode_label');
            $t->integer('y_actual');
            $t->integer('y_prev')->nullable();
            $t->string('prev_state', 10)->nullable(); // A1..A5

            $t->decimal('p1', 10, 4)->default(0);
            $t->decimal('p2', 10, 4)->default(0);
            $t->decimal('p3', 10, 4)->default(0);
            $t->decimal('p4', 10, 4)->default(0);
            $t->decimal('p5', 10, 4)->default(0);

            $t->decimal('f_value', 10, 2)->nullable(); // F(t)

            $t->timestamps();

            $t->foreign('universe_id')->references('id')->on('fts_universes')->cascadeOnDelete();
            $t->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
            $t->foreign('matrix_id')->references('id')->on('fts_markov_matrices')->cascadeOnDelete();

            $t->unique(['interval_set_id','urut'], 'fts_forecasts_iset_urut_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_forecasts');
    }
};
