<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('fts_flrs', function (Blueprint $t) {
            $t->id();
            $t->unsignedBigInteger('interval_set_id')->index();

            $t->integer('urut_from');        // 1..N-1
            $t->integer('urut_to');          // 2..N

            $t->string('periode_from');
            $t->string('periode_to');

            $t->string('state_from', 10);    // A1..A5
            $t->string('state_to', 10);      // A1..A5

            $t->timestamps();

            $t->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
            $t->unique(['interval_set_id','urut_from'], 'fts_flrs_set_from_unique');
        });
    }
    public function down(): void {
        Schema::dropIfExists('fts_flrs');
    }
};
