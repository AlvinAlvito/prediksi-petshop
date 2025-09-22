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
use App\Models\FtsForecast;
use App\Models\FtsMapeSummary;
use App\Models\FtsFutureForecast;
use Illuminate\Support\Arr;


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
        // 1) Ambil baris penjualan terbaru atau berdasarkan ?id=
        $row = Penjualan::when($request->id, fn($q) => $q->where('id', $request->id))
            ->orderByDesc('id')->first();

        if (!$row) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Belum ada data penjualan. Silakan tambahkan data terlebih dahulu.');
        }

        // 2) Deret 12 bulan (Apr 2024 – Mar 2025) → label & nilai
        [$series, $values] = $this->buildSeriesFromRow($row);
        $labels = array_map(fn($r) => $r['label'], $series);
        $actual = array_map(fn($r) => (int) $r['jumlah'], $series);

        // 3) Pastikan pipeline tersedia (universe → intervals → fuzzy → FLR → FLRG → R → forecast → MAPE → future)
        $universe = FtsUniverse::where('produk', $row->nama_produk)
            ->whereNull('periode_mulai')->whereNull('periode_selesai')->first();

        if (!$universe) {
            $universe = $this->computeUniverse($row->nama_produk, null, null, $values, 2, 2, $series);
        }

        $set = FtsIntervalSet::where('universe_id', $universe->id)->where('method', 'sturges')->first();
        if (!$set)
            $set = $this->computeIntervalsFromUniverse($universe, count($values));

        $this->ensureFuzzySetsForIntervalSet($set);
        $this->computeFuzzifications($universe, $set, $series, $values);
        $this->computeFLR($set);
        $this->computeFLRG($set);
        $matrix = $this->computeMarkovMatrix($set);
        $this->computeInitialForecasts($universe, $set, $matrix);
        $this->computeMAPE($set);
        $this->computeFuture7($universe, $set, $matrix);

        // 4) Ambil data ringkas untuk tabel (supaya view tetap jalan)
        $intervals = $set->intervals()->select('urut', 'kode', 'lower_bound', 'upper_bound', 'mid_point')->orderBy('urut')->get();
        $fuzzySets = FtsFuzzySet::where('interval_set_id', $set->id)
    ->select('kode','urut','mu_u1','mu_u2','mu_u3','mu_u4','mu_u5')
    ->orderBy('urut')->get();
        $fuzzis = FtsFuzzification::where('interval_set_id', $set->id)->select('urut', 'periode_label', 'nilai', 'fuzzy_kode')->orderBy('urut')->get();
        $flrs = FtsFlr::where('interval_set_id', $set->id)->select('urut_from', 'urut_to', 'state_from', 'state_to')->orderBy('urut_from')->get();
        $cells = FtsMarkovCell::where('matrix_id', $matrix->id)->select('row_state', 'col_state', 'prob')->orderBy('row_state')->orderBy('col_state')->get();
        $forecasts = FtsForecast::where('interval_set_id', $set->id)->select('urut', 'periode_label', 'y_actual', 'f_value', 'dt', 'f_final', 'f_final_round', 'ape')->orderBy('urut')->get();
        $mape = FtsMapeSummary::where('interval_set_id', $set->id)->first();
        $future = FtsFutureForecast::where('interval_set_id', $set->id)->select('seq', 'periode_label', 'f_round')->orderBy('seq')->get();

        // 5) BANGUN DATA CHART (primitif arrays saja) — dipisah ke helper
        $charts = $this->buildChartsPayload(
            labels: $labels,
            actual: $actual,
            forecasts: $forecasts,
            cells: $cells,
            mape: $mape,
            future: $future
        );

        return view('admin.fts-semesta', [
            'produk' => $row->nama_produk,
            'series' => $series,
            'universe' => $universe,
            'iset' => $set,
            'intervals' => $intervals,
            'fuzzis' => $fuzzis,
            'flrs' => $flrs,
            'markov' => $matrix,
            'markovCells' => $cells,
            'forecasts' => $forecasts,
            'mape' => $mape,
            'future' => $future,
            'fuzzySets'   => $fuzzySets,

            // hanya satu objek untuk semua chart → lebih ringan & jelas tipenya
            'charts' => $charts,
        ]);
    }

    /**
     * Build payload data untuk ApexCharts.
     * @param array<int,string> $labels
     * @param array<int,int>    $actual
     * @return array{
     *   aktual_vs_forecast: array{labels: array<int,string>, aktual: array<int,int>, forecast: array<int,?int>},
     *   error_mape: array{labels: array<int,string>, ape: array<int,float>, mape: float},
     *   markov_heatmap: array<int,array{name:string,data:array<int,array{x:string,y:float}>>> ,
     *   future: array{labels: array<int,string>, values: array<int,int>}
     * }
     */
    private function buildChartsPayload(array $labels, array $actual, $forecasts, $cells, $mape, $future): array
    {
        // Forecast bulat per urut (sinkron ke label indeks)
        $fcByUrut = [];
        foreach ($forecasts as $f) {
            $fcByUrut[(int) $f->urut] = $f->f_final_round; // bisa null untuk t=1
        }
        $forecastSeries = [];
        for ($i = 1; $i <= count($labels); $i++) {
            $forecastSeries[] = $fcByUrut[$i] ?? null;
        }

        // APE & labelnya (hanya t>=2)
        $ape = [];
        $apeLabels = [];
        foreach ($forecasts as $f) {
            if ((int) $f->urut >= 2 && $f->ape !== null) {
                $ape[] = (float) $f->ape * 100.0; // tampilkan dalam %
                $apeLabels[] = $f->periode_label;
            }
        }
        $mapePct = $mape?->mape_pct ?? 0.0;

        // Heatmap Markov (A1..A5)
        $states = ['A1', 'A2', 'A3', 'A4', 'A5'];
        $rows = [];
        foreach ($states as $rowState) {
            $row = ['name' => $rowState, 'data' => []];
            foreach ($states as $colState) {
                /** @var \App\Models\FtsMarkovCell|null $cell */
                $cell = $cells->firstWhere(fn($c) => $c->row_state === $rowState && $c->col_state === $colState);
                $row['data'][] = ['x' => $colState, 'y' => round((float) ($cell->prob ?? 0), 2)];
            }
            $rows[] = $row;
        }

        // Future forecast 7 periode
        $futureLabels = [];
        $futureValues = [];
        foreach ($future as $ff) {
            $futureLabels[] = $ff->periode_label;
            $futureValues[] = (int) $ff->f_round;
        }

        return [
            'aktual_vs_forecast' => [
                'labels' => $labels,
                'aktual' => $actual,
                'forecast' => $forecastSeries,
            ],
            'error_mape' => [
                'labels' => $apeLabels,
                'ape' => $ape,
                'mape' => (float) $mapePct,
            ],
            'markov_heatmap' => $rows,
            'future' => [
                'labels' => $futureLabels,
                'values' => $futureValues,
            ],
        ];
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
        $vals = $values;
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

        $universe = $this->computeUniverse(
            produk: $row->nama_produk,
            periodeMulai: null,
            periodeSelesai: null,
            values: $values,
            d1: $d1,
            d2: $d2,
            series: $series
        );

        $set = $this->computeIntervalsFromUniverse($universe, count($values));

        $this->ensureFuzzySetsForIntervalSet($set);
        $this->computeFuzzifications($universe, $set, $series, $values);
        $this->computeFLR($set);
        $this->computeFLRG($set);
        $this->computeMarkovMatrix($set);
        $matrix = $this->computeMarkovMatrix($set);
        $this->computeInitialForecasts($universe, $set, $matrix);
        $this->computeMAPE($set);
        $this->computeFuture7($universe, $set, $matrix);



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
        FtsFuzzySet::where('interval_set_id', $set->id)->delete();

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

    protected function computeInitialForecasts(
        FtsUniverse $universe,
        FtsIntervalSet $set,
        FtsMarkovMatrix $matrix
    ): void {
        $fuzzis = FtsFuzzification::where('interval_set_id', $set->id)->orderBy('urut')->get();
        $intervals = $set->intervals()->orderBy('urut')->get();

        // mid-point m1..m5
        $m = [];
        foreach ($intervals as $i => $iv) {
            $m[$i + 1] = (float) $iv->mid_point; // 1-based
        }

        // Probabilitas R
        $cells = FtsMarkovCell::where('matrix_id', $matrix->id)->get();
        $P = [];
        foreach ($cells as $c) {
            $P[$c->row_state][$c->col_state] = (float) $c->prob;
        }

        // bersihkan hasil lama
        FtsForecast::where('interval_set_id', $set->id)->delete();

        if ($fuzzis->count() === 0)
            return;

        // t=1: tanpa forecast & Dt
        $first = $fuzzis[0];
        FtsForecast::create([
            'universe_id' => $universe->id,
            'interval_set_id' => $set->id,
            'matrix_id' => $matrix->id,
            'produk' => $universe->produk,
            'urut' => $first->urut,
            'periode_label' => $first->periode_label,
            'y_actual' => (int) $first->nilai,
            'y_prev' => null,
            'prev_state' => null,
            'curr_state' => null,
            'next_state' => null,
            'p1' => 0,
            'p2' => 0,
            'p3' => 0,
            'p4' => 0,
            'p5' => 0,
            'f_value' => null,
            'dt' => null,
        ]);

        $halfL = (float) $set->l_interval / 2.0; // 34.8 / 2 = 17.4

        // t=2..N
        for ($i = 1; $i < $fuzzis->count(); $i++) {
            $prev = $fuzzis[$i - 1];   // t-1
            $curr = $fuzzis[$i];     // t

            $rowState = $prev->fuzzy_kode; // current state (t-1) → baris R
            $yPrev = (float) $prev->nilai;

            $p = [
                1 => (float) ($P[$rowState]['A1'] ?? 0.0),
                2 => (float) ($P[$rowState]['A2'] ?? 0.0),
                3 => (float) ($P[$rowState]['A3'] ?? 0.0),
                4 => (float) ($P[$rowState]['A4'] ?? 0.0),
                5 => (float) ($P[$rowState]['A5'] ?? 0.0),
            ];

            $iState = (int) str_replace('A', '', $rowState);          // index current
            $jState = (int) str_replace('A', '', $curr->fuzzy_kode);  // index next

            // Forecast awal
            $F = 0.0;
            for ($j = 1; $j <= 5; $j++) {
                $weight = $p[$j];
                if ($weight == 0)
                    continue;
                $val = ($j === $iState) ? $yPrev : $m[$j];
                $F += $val * $weight;
            }

            // Penyesuaian Dt
            $deltaIdx = $jState - $iState;         // bisa negatif
            $Dt = $deltaIdx * $halfL;        // (j-i) * (l/2)

            $FprimeDec = round($F + $Dt, 2);                              // mis. 212.50
            $FprimeInt = (int) round($FprimeDec, 0, PHP_ROUND_HALF_UP);   // mis. 213

            FtsForecast::create([
                'universe_id' => $universe->id,
                'interval_set_id' => $set->id,
                'matrix_id' => $matrix->id,
                'produk' => $universe->produk,
                'urut' => $curr->urut,
                'periode_label' => $curr->periode_label,
                'y_actual' => (int) $curr->nilai,
                'y_prev' => (int) $yPrev,
                'prev_state' => $rowState,
                'curr_state' => $rowState,
                'next_state' => $curr->fuzzy_kode,
                'p1' => $p[1],
                'p2' => $p[2],
                'p3' => $p[3],
                'p4' => $p[4],
                'p5' => $p[5],
                'f_value' => round($F, 2),          // F(t)
                'dt' => round($Dt, 1),         // Dt
                'f_final' => $FprimeDec,            // F′(t)
                'f_final_round' => $FprimeInt,            // F′(t) dibulatkan
            ]);
        }
    }

    protected function computeMAPE(FtsIntervalSet $set): void
    {
        $rows = FtsForecast::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        $sum = 0.0;
        $cnt = 0;

        foreach ($rows as $r) {
            // hanya t>=2 yang punya final forecast
            if ($r->f_final_round !== null && $r->urut >= 2 && $r->y_actual > 0) {
                $ape = abs(($r->y_actual - $r->f_final_round) / $r->y_actual);
                $r->ape = round($ape, 5); // simpan 5 desimal
                $r->save();

                $sum += $ape;
                $cnt += 1;
            } else {
                $r->ape = null;
                $r->save();
            }
        }

        $mapePct = $cnt > 0 ? ($sum / $cnt) * 100.0 : 0.0;

        FtsMapeSummary::updateOrCreate(
            ['interval_set_id' => $set->id],
            [
                'n_rows' => $cnt,
                'sum_ape' => round($sum, 6),
                'mape_pct' => round($mapePct, 4),
            ]
        );
    }

    protected function computeFuture7(
        FtsUniverse $universe,
        FtsIntervalSet $set,
        FtsMarkovMatrix $matrix
    ): void {
        // Clear lama
        FtsFutureForecast::where('interval_set_id', $set->id)->delete();

        // Midpoints m1..m5
        $intervals = $set->intervals()->orderBy('urut')->get();
        $m = [];
        foreach ($intervals as $i => $iv)
            $m[$i + 1] = (float) $iv->mid_point;

        // Prob baris A2
        $cells = FtsMarkovCell::where('matrix_id', $matrix->id)->get();
        $p = [
            1 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A1')->prob ?? 0),
            2 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A2')->prob ?? 0),
            3 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A3')->prob ?? 0),
            4 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A4')->prob ?? 0),
            5 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A5')->prob ?? 0),
        ];

        // Y input awal = F′(12) desimal (baris terakhir in-sample)
        $last = FtsForecast::where('interval_set_id', $set->id)->orderByDesc('urut')->first();
        $yIn = (float) ($last?->f_final ?? 0.0);

        // Label bulan: Apr..Okt 2025
        $labels = ['April 2025', 'Mei 2025', 'Juni 2025', 'Juli 2025', 'Agustus 2025', 'September 2025', 'Oktober 2025'];

        for ($seq = 1; $seq <= 7; $seq++) {
            // F = m1*P21 + Y*P22 + m3*P23 + m4*P24 + m5*P25
            $F = $m[1] * $p[1] + $yIn * $p[2] + $m[3] * $p[3] + $m[4] * $p[4] + $m[5] * $p[5];
            $Fdec = round($F, 2);
            $Fround = (int) round($Fdec, 0, PHP_ROUND_HALF_UP);

            FtsFutureForecast::create([
                'interval_set_id' => $set->id,
                'matrix_id' => $matrix->id,
                'produk' => $universe->produk,
                'start_state' => 'A2',
                'seq' => $seq,
                'periode_label' => $labels[$seq - 1],
                'y_input' => round($yIn, 2),
                'p1' => $p[1],
                'p2' => $p[2],
                'p3' => $p[3],
                'p4' => $p[4],
                'p5' => $p[5],
                'f_value' => $Fdec,
                'f_round' => $Fround,
            ]);

            // untuk step berikutnya, Y input = hasil desimal sekarang
            $yIn = $Fdec;
        }
    }

}
