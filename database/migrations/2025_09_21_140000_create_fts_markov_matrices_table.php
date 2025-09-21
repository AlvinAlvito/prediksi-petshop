<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_markov_matrices', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('interval_set_id')->unique();
            $t->integer('k_state')->default(0);
            $t->timestamps();

            $t->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_markov_matrices');
    }
};
