<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fts_intervals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('interval_set_id')->index();

            $table->string('kode', 10); // u1..uK
            $table->integer('urut');    // 1..K

            $table->decimal('lower_bound', 10, 4);
            $table->decimal('upper_bound', 10, 4);
            $table->decimal('mid_point', 10, 4);

            $table->timestamps();

            $table->foreign('interval_set_id')->references('id')->on('fts_interval_sets')->cascadeOnDelete();
            $table->unique(['interval_set_id','urut'], 'fts_intervals_set_urut_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fts_intervals');
    }
};
