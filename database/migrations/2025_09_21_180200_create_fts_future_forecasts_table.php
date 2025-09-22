<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_future_forecasts', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('interval_set_id')->index();
            $t->unsignedBigInteger('matrix_id')->index();

            $t->string('produk')->index();
            $t->string('start_state', 10)->default('A2'); // sesuai kasus

            $t->integer('seq');                  // 1..7
            $t->string('periode_label');         // April 2025, dst (ID)
            $t->decimal('y_input', 10, 2)->default(0);

            $t->decimal('p1', 10, 4)->default(0);
            $t->decimal('p2', 10, 4)->default(0);
            $t->decimal('p3', 10, 4)->default(0);
            $t->decimal('p4', 10, 4)->default(0);
            $t->decimal('p5', 10, 4)->default(0);

            $t->decimal('f_value', 10, 2)->default(0); // desimal
            $t->integer('f_round')->default(0);        // dibulatkan

            $t->timestamps();

            $t->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
            $t->foreign('matrix_id')->references('id')->on('fts_markov_matrices')->cascadeOnDelete();

            $t->unique(['interval_set_id','seq'], 'future_fc_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_future_forecasts');
    }
};
