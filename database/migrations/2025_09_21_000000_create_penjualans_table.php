<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('nama_produk')->index();
            $table->integer('harga_satuan')->default(0);

            // 12 bulan
            $table->integer('jan')->default(0);
            $table->integer('feb')->default(0);
            $table->integer('mar')->default(0);
            $table->integer('apr')->default(0);
            $table->integer('mei')->default(0);
            $table->integer('jun')->default(0);
            $table->integer('jul')->default(0);
            $table->integer('agu')->default(0);
            $table->integer('sep')->default(0);
            $table->integer('okt')->default(0);
            $table->integer('nov')->default(0);
            $table->integer('des')->default(0);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
