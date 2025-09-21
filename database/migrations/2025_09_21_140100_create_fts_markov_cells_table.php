<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_markov_cells', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('matrix_id')->index();

            $t->string('row_state', 10); // A1..Ak
            $t->string('col_state', 10); // A1..Ak
            $t->integer('freq')->default(0);
            $t->integer('row_total')->default(0);
            $t->decimal('prob', 10, 4)->default(0);

            $t->timestamps();

            $t->foreign('matrix_id')->references('id')->on('fts_markov_matrices')->cascadeOnDelete();
            $t->unique(['matrix_id','row_state','col_state'], 'fts_markov_cells_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_markov_cells');
    }
};
