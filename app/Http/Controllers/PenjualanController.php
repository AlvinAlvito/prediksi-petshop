<?php

namespace App\Http\Controllers;

use App\Models\FtsUniverse;
use App\Models\Penjualan;
use Illuminate\Http\Request;
use App\Models\FtsIntervalSet;
use App\Models\FtsInterval;
use App\Models\FtsFuzzySet;
use App\Models\FtsFuzzification;
use App\Models\FtsFlr;
use App\Models\FtsFlrgItem;
use App\Models\FtsMarkovMatrix;
use App\Models\FtsMarkovCell;

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

        $intervals = $set->intervals()->get();

        $fuzzySets = FtsFuzzySet::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        $fuzzis = FtsFuzzification::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        $flrs = FtsFlr::where('interval_set_id', $set->id)->orderBy('urut_from')->get();

        $flrgRaw = FtsFlrgItem::where('interval_set_id', $set->id)
            ->orderBy('current_state')
            ->orderBy('next_state')
            ->get();

        $flrg = [];
        foreach ($flrgRaw as $it) {
            $flrg[$it->current_state][$it->next_state] = $it->freq;
        }

        $matrix = FtsMarkovMatrix::where('interval_set_id', $set->id)->first();
        $cells = $matrix ? $matrix->cells()->orderBy('row_state')->orderBy('col_state')->get() : collect();

        return view('admin.fts-semesta', [
            // ...payload lama...
            'produk' => $row->nama_produk,
            'series' => $series,
            'universe' => $universe,
            'iset' => $set,
            'intervals' => $intervals,
            'fuzzySets' => $fuzzySets,
            'fuzzis' => $fuzzis,
            'flrs' => $flrs,
            'flrg' => $flrg,
            // baru:
            'markov' => $matrix,
            'markovCells' => $cells,
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
        $set = $this->computeIntervalsFromUniverse($universe, count($values));

        $this->ensureFuzzySetsForIntervalSet($set);
        $this->computeFuzzifications($universe, $set, $series, $values);
        $this->computeFLR($set);
        $this->computeFLRG($set);
        $this->computeMarkovMatrix($set);

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

    protected function ensureFuzzySetsForIntervalSet(FtsIntervalSet $set): void
    {
        // Hapus & rebuild agar selalu konsisten dengan k=5
        FtsFuzzySet::where('interval_set_id', $set->id)->delete();

        // Matriks membership sesuai instruksi:
        // A1=1/u1 + 0.5/u2
        // A2=0.5/u1 + 1/u2 + 0.5/u3
        // A3=0.5/u2 + 1/u3 + 0.5/u4
        // A4=0.5/u3 + 1/u4 + 0.5/u5
        // A5=0.5/u4 + 1/u5
        $rows = [
            ['kode' => 'A1', 'urut' => 1, 'mu' => [1, 0.5, 0, 0, 0]],
            ['kode' => 'A2', 'urut' => 2, 'mu' => [0.5, 1, 0.5, 0, 0]],
            ['kode' => 'A3', 'urut' => 3, 'mu' => [0, 0.5, 1, 0.5, 0]],
            ['kode' => 'A4', 'urut' => 4, 'mu' => [0, 0, 0.5, 1, 0.5]],
            ['kode' => 'A5', 'urut' => 5, 'mu' => [0, 0, 0, 0.5, 1]],
        ];

        foreach ($rows as $r) {
            FtsFuzzySet::create([
                'interval_set_id' => $set->id,
                'kode' => $r['kode'],
                'urut' => $r['urut'],
                'mu_u1' => $r['mu'][0],
                'mu_u2' => $r['mu'][1],
                'mu_u3' => $r['mu'][2],
                'mu_u4' => $r['mu'][3],
                'mu_u5' => $r['mu'][4],
            ]);
        }
    }

    protected function computeFuzzifications(
        FtsUniverse $universe,
        FtsIntervalSet $set,
        array $series, // [['label'=>'April 2024','jumlah'=>174], ...] urutan April..Maret
        array $values  // [174,156,...]
    ): void {
        // Hapus hasil lama untuk set ini agar konsisten
        FtsFuzzification::where('interval_set_id', $set->id)->delete();

        $intervals = $set->intervals()->get(); // u1..u5
        $k = $intervals->count();

        foreach ($series as $i => $row) {
            $x = (float) $row['jumlah'];

            // Cari u_j yang memuat x (inklusif batas atas untuk interval terakhir)
            $chosen = null;
            foreach ($intervals as $idx => $iv) {
                $lower = (float) $iv->lower_bound;
                $upper = (float) $iv->upper_bound;

                $in = ($idx < $k - 1)
                    ? ($x >= $lower && $x < $upper)   // interval biasa: [lower, upper)
                    : ($x >= $lower && $x <= $upper); // interval terakhir: [lower, upper]

                if ($in) {
                    $chosen = $iv;
                    break;
                }
            }

            // Map u_j -> A_j (sesuai tabel kamu)
            $fuzzyKode = $chosen ? ('A' . $chosen->urut) : null;
            $intKode = $chosen ? $chosen->kode : null;

            FtsFuzzification::create([
                'universe_id' => $universe->id,
                'interval_set_id' => $set->id,
                'produk' => $universe->produk,
                'urut' => $i + 1,
                'periode_label' => $row['label'],
                'nilai' => (int) $row['jumlah'],
                'interval_kode' => $intKode,       // u1..u5
                'fuzzy_kode' => $fuzzyKode,     // A1..A5
            ]);
        }
    }

    protected function computeFLR(FtsIntervalSet $set): void
    {
        // Bersihkan data lama agar konsisten
        FtsFlr::where('interval_set_id', $set->id)->delete();

        $rows = FtsFuzzification::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        for ($i = 0; $i < $rows->count() - 1; $i++) {
            $cur = $rows[$i];
            $nxt = $rows[$i + 1];

            FtsFlr::create([
                'interval_set_id' => $set->id,
                'urut_from' => $cur->urut,
                'urut_to' => $nxt->urut,
                'periode_from' => $cur->periode_label,
                'periode_to' => $nxt->periode_label,
                'state_from' => $cur->fuzzy_kode, // Ai
                'state_to' => $nxt->fuzzy_kode, // Aj
            ]);
        }
    }

    protected function computeFLRG(FtsIntervalSet $set): void
    {
        FtsFlrgItem::where('interval_set_id', $set->id)->delete();

        $flrs = FtsFlr::where('interval_set_id', $set->id)->orderBy('urut_from')->get();

        // Kelompokkan berdasarkan current_state lalu hitung frekuensi next_state
        $grouped = [];
        foreach ($flrs as $r) {
            $cs = $r->state_from;  // A1..A5
            $ns = $r->state_to;    // A1..A5
            if (!isset($grouped[$cs]))
                $grouped[$cs] = [];
            if (!isset($grouped[$cs][$ns]))
                $grouped[$cs][$ns] = 0;
            $grouped[$cs][$ns] += 1;
        }

        foreach ($grouped as $cs => $nexts) {
            foreach ($nexts as $ns => $freq) {
                FtsFlrgItem::create([
                    'interval_set_id' => $set->id,
                    'current_state' => $cs,
                    'next_state' => $ns,
                    'freq' => $freq,
                ]);
            }
        }
    }
    protected function computeMarkovMatrix(FtsIntervalSet $set): FtsMarkovMatrix
    {
        // Ambil FLRG (freq per current_state -> next_state)
        $items = FtsFlrgItem::where('interval_set_id', $set->id)->get();

        // Daftar state A1..Ak (urut)
        $k = (int) $set->k_interval;
        $states = [];
        for ($i = 1; $i <= $k; $i++)
            $states[] = 'A' . $i;

        // Siapkan row totals
        $rowTotals = array_fill_keys($states, 0);
        foreach ($items as $it) {
            $rowTotals[$it->current_state] += (int) $it->freq;
        }

        // Buat/refresh header matriks
        $matrix = FtsMarkovMatrix::updateOrCreate(
            ['interval_set_id' => $set->id],
            ['k_state' => $k]
        );

        // Hapus isi lama
        FtsMarkovCell::where('matrix_id', $matrix->id)->delete();

        // Isi sel (termasuk 0) untuk seluruh pasangan (Ai, Aj)
        foreach ($states as $rs) {
            $den = max(0, (int) ($rowTotals[$rs] ?? 0)); // bisa 0 (baris kosong)
            foreach ($states as $cs) {
                $freq = (int) ($items->firstWhere(fn($x) => $x->current_state === $rs && $x->next_state === $cs)->freq ?? 0);
                $prob = $den > 0 ? $freq / $den : 0.0;

                FtsMarkovCell::create([
                    'matrix_id' => $matrix->id,
                    'row_state' => $rs,
                    'col_state' => $cs,
                    'freq' => $freq,
                    'row_total' => $den,
                    'prob' => $prob,
                ]);
            }
        }

        return $matrix;
    }





}
