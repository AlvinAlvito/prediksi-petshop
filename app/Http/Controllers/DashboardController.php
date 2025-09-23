<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Penjualan;
use App\Models\FtsMapeSummary;
use App\Models\FtsForecast;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Total ringkasan
        $totalProduk = Penjualan::count();
        $totalPegawai = 6; // kalau ada tabel pegawai bisa diganti query
        $totalPenjualan = Penjualan::selectRaw('SUM(jan+feb+mar+apr+mei+jun+jul+agu+sep+okt+nov+des) as total')->value('total') ?? 0;

        // Chart 1: Total penjualan per produk
        $produkData = Penjualan::all();
        $chartTotalProduk = [
            'labels' => $produkData->pluck('nama_produk'),
            'values' => $produkData->map(
                fn($p) =>
                $p->jan + $p->feb + $p->mar + $p->apr + $p->mei + $p->jun +
                $p->jul + $p->agu + $p->sep + $p->okt + $p->nov + $p->des
            ),
        ];

        $mapes = DB::table('fts_mape_summaries as ms')
            ->join('fts_interval_sets as iset', 'iset.id', '=', 'ms.interval_set_id')
            ->select('iset.produk', 'ms.mape_pct')
            ->get();

        $chartMape = [
            'labels' => $mapes->pluck('produk'),
            'values' => $mapes->pluck('mape_pct')->map(fn($v) => (float) $v)->values(),
        ];

        // Chart 3: Total penjualan per bulan (akumulasi semua produk)
        $bulanLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $bulanTotals = array_fill(0, 12, 0);
        foreach ($produkData as $p) {
            $bulanTotals[0] += $p->jan;
            $bulanTotals[1] += $p->feb;
            $bulanTotals[2] += $p->mar;
            $bulanTotals[3] += $p->apr;
            $bulanTotals[4] += $p->mei;
            $bulanTotals[5] += $p->jun;
            $bulanTotals[6] += $p->jul;
            $bulanTotals[7] += $p->agu;
            $bulanTotals[8] += $p->sep;
            $bulanTotals[9] += $p->okt;
            $bulanTotals[10] += $p->nov;
            $bulanTotals[11] += $p->des;
        }
        $chartTotalBulan = [
            'labels' => $bulanLabels,
            'values' => $bulanTotals,
        ];

        // Chart 4: Top 5 produk terlaris
        $topProduk = $produkData->map(function ($p) {
            $total = $p->jan + $p->feb + $p->mar + $p->apr + $p->mei + $p->jun +
                $p->jul + $p->agu + $p->sep + $p->okt + $p->nov + $p->des;
            return ['nama' => $p->nama_produk, 'total' => $total];
        })->sortByDesc('total')->take(5);

        $chartTopProduk = [
            'labels' => $topProduk->pluck('nama'),
            'values' => $topProduk->pluck('total'),
        ];

        // Chart 5: Aktual vs Forecast (ambil produk pertama saja)
        $firstForecasts = FtsForecast::orderBy('urut')->limit(12)->get();
        $chartBandingAktual = [
            'labels' => $firstForecasts->pluck('periode_label'),
            'aktual' => $firstForecasts->pluck('y_actual'),
            'forecast' => $firstForecasts->pluck('f_final_round'),
        ];

        $charts = [
            'total_produk' => $chartTotalProduk,
            'mape' => $chartMape,
            'total_bulan' => $chartTotalBulan,
            'top_produk' => $chartTopProduk,
            'banding_aktual' => $chartBandingAktual,
        ];

        return view('admin.index', compact('totalProduk', 'totalPegawai', 'totalPenjualan', 'charts'));
    }
}
