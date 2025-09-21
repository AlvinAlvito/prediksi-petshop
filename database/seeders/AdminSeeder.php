<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    public function run()
    {
        // Cek dulu kalau sudah ada, jangan insert ulang
        if (!User::where('username', 'admin')->exists()) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@example.com', 
                'username' => 'admin',
                'password' => Hash::make('123'),
            ]);
        }
    }
}