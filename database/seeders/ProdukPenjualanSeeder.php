<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProdukPenjualan;
use App\Models\HasilRegresi;

class ProdukPenjualanSeeder extends Seeder
{
    public function run()
    {
        $json = file_get_contents(database_path('data/data_produk.json'));
        $produkList = json_decode($json, true);

        foreach ($produkList as $data) {
            $produk = ProdukPenjualan::create([
                'nama_produk' => $data['nama_produk'],
                'harga_satuan' => 0, // default sementara
                'jan' => $data['jan'],
                'feb' => $data['feb'],
                'mar' => $data['mar'],
                'apr' => $data['apr'],
                'mei' => $data['mei'],
                'jun' => $data['jun'],
            ]);

            // === Proses Regresi ===
            $x = [1, 2, 3, 4, 5, 6];
            $y = [
                $data['jan'], $data['feb'], $data['mar'],
                $data['apr'], $data['mei'], $data['jun']
            ];

            $n = count($x);
            $sum_x = array_sum($x);
            $sum_y = array_sum($y);
            $sum_x2 = array_sum(array_map(fn($v) => $v * $v, $x));
            $sum_xy = array_sum(array_map(fn($x, $y) => $x * $y, $x, $y));

            $b = ($n * $sum_xy - $sum_x * $sum_y) / ($n * $sum_x2 - pow($sum_x, 2));
            $a = ($sum_y - $b * $sum_x) / $n;
            $persamaan = "Y = " . round($a, 3) . " + " . round($b, 3) . "X";

            $prediksi = [];
            $prediksi_desimal = [];

            for ($i = 1; $i <= 12; $i++) {
                $nilai = $a + $b * $i;
                $prediksi[] = round($nilai);
                $prediksi_desimal[] = round($nilai, 3);
            }

            $total_error = 0;
            foreach ($x as $i => $xi) {
                $y_pred = $prediksi_desimal[$i];
                $error = abs($y[$i] - $y_pred) / max($y[$i], 1);
                $total_error += $error;
            }
            $mape = round(($total_error / $n) * 100, 2);

            HasilRegresi::create([
                'produk_id' => $produk->id,
                'a' => $a,
                'b' => $b,
                'persamaan' => $persamaan,
                'mape' => $mape,
                'jan' => $prediksi[0],
                'feb' => $prediksi[1],
                'mar' => $prediksi[2],
                'apr' => $prediksi[3],
                'mei' => $prediksi[4],
                'jun' => $prediksi[5],
                'jul' => $prediksi[6],
                'agu' => $prediksi[7],
                'sep' => $prediksi[8],
                'okt' => $prediksi[9],
                'nov' => $prediksi[10],
                'des' => $prediksi[11],
            ]);
        }
    }
}
