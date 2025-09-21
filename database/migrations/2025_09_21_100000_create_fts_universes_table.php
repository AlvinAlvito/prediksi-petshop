<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fts_universes', function (Blueprint $table) {
            $table->id();
            $table->string('produk')->index();
            $table->date('periode_mulai')->nullable();
            $table->date('periode_selesai')->nullable();

            $table->integer('n')->default(0);
            $table->integer('dmin')->default(0);
            $table->integer('dmax')->default(0);
            $table->integer('d1')->default(0);
            $table->integer('d2')->default(0);
            $table->integer('u_min')->default(0);
            $table->integer('u_max')->default(0);

            $table->json('input_series')->nullable();

            $table->timestamps();

            $table->unique(['produk', 'periode_mulai', 'periode_selesai'], 'fts_universes_prod_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fts_universes');
    }
};
