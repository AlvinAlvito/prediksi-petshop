<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fts_interval_sets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('universe_id')->index();
            $table->string('produk')->index();

            $table->integer('n_period')->default(0);
            $table->integer('k_interval')->default(0);
            $table->decimal('l_interval', 10, 4)->default(0);

            $table->string('method')->default('sturges');
            $table->integer('u_min')->default(0);
            $table->integer('u_max')->default(0);

            $table->timestamps();

            $table->foreign('universe_id')->references('id')->on('fts_universes')->cascadeOnDelete();

            $table->unique(['universe_id', 'method'], 'fts_iset_universe_method_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fts_interval_sets');
    }
};
