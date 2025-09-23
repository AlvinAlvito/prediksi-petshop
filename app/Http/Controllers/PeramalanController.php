<?php

namespace App\Http\Controllers;

use App\Models\FtsFutureForecast;
use App\Models\Penjualan;

class PeramalanController extends Controller
{
    public function index()
    {
        $rows = FtsFutureForecast::orderBy('produk')
            ->orderBy('seq')
            ->get()
            ->groupBy('produk');

        $peramalan = [];
        foreach ($rows as $produk => $list) {
            $penjualanId = Penjualan::where('nama_produk', $produk)->latest('id')->value('id');
            $peramalan[] = [
                'produk' => $produk,
                'penjualan_id' => $penjualanId,
                'values' => $list->pluck('f_round', 'periode_label')->toArray(),
            ];
        }

        return view('admin.data-peramalan', compact('peramalan'));
    }
}
