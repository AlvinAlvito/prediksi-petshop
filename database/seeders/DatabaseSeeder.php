<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Panggil seeder Admin
        $this->call([
            AdminSeeder::class,
        ]);
        $this->call(ProdukPenjualanSeeder::class);

    }
}
