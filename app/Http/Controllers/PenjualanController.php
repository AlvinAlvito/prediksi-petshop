<?php

namespace App\Http\Controllers;

use App\Models\FtsUniverse;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Models\FtsIntervalSet;
use App\Models\FtsInterval;

class PenjualanController extends Controller
{
    // LIST (dipanggil dari closure GET /admin/data-penjualan)
    public function index(Request $request)
    {
        $penjualan = Penjualan::orderBy('id', 'asc')->get();
        return view('admin.data-penjualan', compact('penjualan'));
    }

    // STORE (dipanggil dari closure POST /admin/data-penjualan)
    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $row = Penjualan::create($data);

        // ===== Jalankan Proses Tahap 1: Semesta U berbasis DB =====
        $this->processSemestaUFromRow($row);

        // Arahkan ke halaman ringkasan semesta supaya hasilnya langsung terlihat
        return redirect()->route('fts.semesta', ['id' => $row->id])
            ->with('success', 'Data penjualan berhasil ditambahkan & Semesta U diproses.');
    }

    // UPDATE (dipanggil dari closure PUT /admin/data-penjualan/{id})
    public function update(Request $request, $id)
    {
        $row = Penjualan::findOrFail($id);
        $data = $this->validateData($request, $row->id);

        $row->update($data);

        // (Opsional) kalau kamu ingin re-proses Semesta U saat update:
        // $this->processSemestaUFromRow($row);

        return redirect()->route('penjualan.index')
            ->with('success', 'Data penjualan berhasil diperbarui.');
    }

    // DESTROY (dipanggil dari closure DELETE /admin/data-penjualan/{id})
    public function destroy($id)
    {
        $row = Penjualan::findOrFail($id);
        $row->delete();

        return redirect()->route('penjualan.index')
            ->with('success', 'Data penjualan berhasil dihapus.');
    }

    // ====== VALIDASI ======
    protected function validateData(Request $request, $ignoreId = null): array
    {
        // Field bulan boleh 0 / kosong → set default 0
        $bulanRules = ['nullable', 'integer', 'min:0'];

        $validated = $request->validate([
            'nama_produk' => ['required', 'string', 'max:255'],
            'harga_satuan' => ['required', 'integer', 'min:0'],
            'jan' => $bulanRules,
            'feb' => $bulanRules,
            'mar' => $bulanRules,
            'apr' => $bulanRules,
            'mei' => $bulanRules,
            'jun' => $bulanRules,
            'jul' => $bulanRules,
            'agu' => $bulanRules,
            'sep' => $bulanRules,
            'okt' => $bulanRules,
            'nov' => $bulanRules,
            'des' => $bulanRules,
        ], [], [
            'nama_produk' => 'Nama Produk',
            'harga_satuan' => 'Harga Satuan',
        ]);

        // Normalisasi nilai null jadi 0 agar aman dihitung
        foreach (['jan', 'feb', 'mar', 'apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des'] as $m) {
            $validated[$m] = (int) ($validated[$m] ?? 0);
        }

        return $validated;
    }

    // ======================== TAHAP 1: SEMESTA U ========================

    // View ringkasan Semesta U (GET /admin/fts/semesta?id=...)
    public function semestaU(Request $request)
    {
        // Ambil baris sesuai id (jika ada), kalau tidak pakai baris terbaru
        $row = Penjualan::when($request->id, fn($q) => $q->where('id', $request->id))
            ->orderByDesc('id')
            ->first(); // <— BUKAN firstOrFail()

        if (!$row) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Belum ada data penjualan. Silakan tambahkan data terlebih dahulu.');
        }

        // Bangun deret (12 bulan) dari DB
        [$series, $values] = $this->buildSeriesFromRow($row);

        // Ambil ringkasan semesta; jika belum ada, hitung sekarang
        $universe = FtsUniverse::where('produk', $row->nama_produk)
            ->whereNull('periode_mulai')->whereNull('periode_selesai')
            ->first();

        if (!$universe) {
            $universe = $this->computeUniverse(
                produk: $row->nama_produk,
                periodeMulai: null,
                periodeSelesai: null,
                values: $values,
                d1: 2,
                d2: 2,
                series: $series
            );
        }
        $set = FtsIntervalSet::where('universe_id', $universe->id)->where('method', 'sturges')->first();
        if (!$set) {
            $set = $this->computeIntervalsFromUniverse($universe, count($values));
        }

        // Ambil daftar interval u1..uK
        $intervals = $set->intervals()->get();

        return view('admin.fts-semesta', [
            'produk' => $row->nama_produk,
            'series' => $series,
            'universe' => $universe,
            'iset' => $set,
            'intervals' => $intervals,
        ]);
    }


    // Bangun array series & values dari 1 baris penjualan (mulai April 2024 s/d Maret 2025)
    protected function buildSeriesFromRow(Penjualan $row): array
    {
        // Urutan field dari April..Desember 2024 lalu Januari..Maret 2025
        $fields = ['apr', 'mei', 'jun', 'jul', 'agu', 'sep', 'okt', 'nov', 'des', 'jan', 'feb', 'mar'];

        // Label bulan dengan tahun sesuai kebutuhan
        $labels = [
            'April 2024',
            'Mei 2024',
            'Juni 2024',
            'Juli 2024',
            'Agustus 2024',
            'September 2024',
            'Oktober 2024',
            'November 2024',
            'Desember 2024',
            'Januari 2025',
            'Februari 2025',
            'Maret 2025'
        ];

        $series = [];
        $values = [];
        foreach ($fields as $i => $f) {
            $v = (int) ($row->$f ?? 0);
            $series[] = ['label' => $labels[$i], 'jumlah' => $v];
            $values[] = $v;
        }
        return [$series, $values];
    }

    // Hitung & simpan Semesta U
    protected function computeUniverse(
        string $produk,
        $periodeMulai,
        $periodeSelesai,
        array $values,
        int $d1,
        int $d2,
        array $series
    ): FtsUniverse {
        $vals = $values;                  // jika ingin abaikan nol, bisa filter di sini
        $n = count($vals);
        $dmin = min($vals);
        $dmax = max($vals);
        $uMin = $dmin - $d1;
        $uMax = $dmax + $d2;

        return FtsUniverse::updateOrCreate(
            [
                'produk' => $produk,
                'periode_mulai' => $periodeMulai,
                'periode_selesai' => $periodeSelesai,
            ],
            [
                'n' => $n,
                'dmin' => $dmin,
                'dmax' => $dmax,
                'd1' => $d1,
                'd2' => $d2,
                'u_min' => $uMin,
                'u_max' => $uMax,
                'input_series' => $series,
            ]
        );
    }

    // Dipanggil setelah create() di store()
    protected function processSemestaUFromRow(Penjualan $row): void
    {
        [$series, $values] = $this->buildSeriesFromRow($row);
        $d1 = 2;
        $d2 = 2;

        // 1) Semesta U
        $universe = $this->computeUniverse(
            produk: $row->nama_produk,
            periodeMulai: null,
            periodeSelesai: null,
            values: $values,
            d1: $d1,
            d2: $d2,
            series: $series
        );

        // 2) Interval (Sturges + panjang interval + daftar u1..uK)
        $this->computeIntervalsFromUniverse($universe, count($values));
    }

    protected function computeIntervalsFromUniverse(FtsUniverse $universe, int $N): FtsIntervalSet
    {
        // --- Sturges ---
        // k = round(1 + 3.322*log10(N))  -> contoh: N=12, 1+3.322*log10(12)=4.584 → 5
        $k = max(1, (int) round(1 + 3.322 * log10(max(1, $N))));

        $range = $universe->u_max - $universe->u_min;      // 328 - 154 = 174
        $l = $k > 0 ? ($range / $k) : $range;          // 174 / 5 = 34.8

        // Buat/Update header set
        $set = FtsIntervalSet::updateOrCreate(
            ['universe_id' => $universe->id, 'method' => 'sturges'],
            [
                'produk' => $universe->produk,
                'n_period' => $N,
                'k_interval' => $k,
                'l_interval' => $l,
                'u_min' => $universe->u_min,
                'u_max' => $universe->u_max,
            ]
        );

        // Hapus interval lama agar konsisten
        FtsInterval::where('interval_set_id', $set->id)->delete();

        // Generate interval u1..uK
        $uMin = (float) $universe->u_min;
        $uMax = (float) $universe->u_max;

        for ($i = 0; $i < $k; $i++) {
            $lower = $uMin + $i * $l;
            $upper = $uMin + ($i + 1) * $l;
            if ($i === $k - 1)
                $upper = $uMax; // pastikan interval terakhir tepat ke u_max

            $mid = ($lower + $upper) / 2.0;

            FtsInterval::create([
                'interval_set_id' => $set->id,
                'kode' => 'u' . ($i + 1),
                'urut' => $i + 1,
                'lower_bound' => $lower,
                'upper_bound' => $upper,
                'mid_point' => $mid,
            ]);
        }

        return $set;
    }

}
