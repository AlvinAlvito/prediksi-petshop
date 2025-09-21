<?php


namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProdukPenjualan;
use App\Models\HasilRegresi;

class DashboardController extends Controller
{
    public function index()
    {
        $produk = ProdukPenjualan::all();
        $regresi = HasilRegresi::with('produk')->get();

        // 1. Total penjualan per produk (Jan–Jun)
        $totalPerProduk = $produk->map(function ($item) {
            return [
                'name' => $item->nama_produk,
                'total' => $item->jan + $item->feb + $item->mar + $item->apr + $item->mei + $item->jun
            ];
        });

        // 2. Prediksi per produk (Jul–Des)
        $prediksiPerProduk = $regresi->map(function ($item) {
            return [
                'name' => $item->produk->nama_produk,
                'data' => [$item->jul, $item->agu, $item->sep, $item->okt, $item->nov, $item->des]
            ];
        });

        // 3. MAPE per produk
        $mapePerProduk = $regresi->map(function ($item) {
            return [
                'name' => $item->produk->nama_produk,
                'mape' => $item->mape
            ];
        });

        // 4. Total penjualan gabungan per bulan (Jan–Jun)
        $totalPerBulan = [
            'Jan' => $produk->sum('jan'),
            'Feb' => $produk->sum('feb'),
            'Mar' => $produk->sum('mar'),
            'Apr' => $produk->sum('apr'),
            'Mei' => $produk->sum('mei'),
            'Jun' => $produk->sum('jun')
        ];

        // 5. Top 5 Produk terlaris
        $topProduk = $totalPerProduk->sortByDesc('total')->take(5);

        return view('admin.index', [
            'totalPerProduk' => $totalPerProduk,
            'prediksiPerProduk' => $prediksiPerProduk,
            'mapePerProduk' => $mapePerProduk,
            'totalPerBulan' => $totalPerBulan,
            'topProduk' => $topProduk,
        ]);
    }
}
