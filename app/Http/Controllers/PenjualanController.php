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
use Illuminate\Support\Collection;

class PenjualanController extends Controller
{
    public function index(Request $request)
    {
        $penjualan = Penjualan::orderBy('id', 'asc')->get();
        return view('admin.data-penjualan', compact('penjualan'));
    }

    public function store(Request $request)
    {
        $data = $this->validateData($request);
        $row = Penjualan::create($data);
        $this->processSemestaUFromRow($row);

        return redirect()->route('fts.semesta', ['id' => $row->id])
            ->with('success', 'Data penjualan berhasil ditambahkan & Semesta U diproses.');
    }

    public function update(Request $request, $id)
    {
        $row = Penjualan::findOrFail($id);
        $data = $this->validateData($request, $row->id);
        $row->update($data);

        return redirect()->route('penjualan.index')
            ->with('success', 'Data penjualan berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $row = Penjualan::findOrFail($id);
        $row->delete();

        return redirect()->route('penjualan.index')
            ->with('success', 'Data penjualan berhasil dihapus.');
    }

    protected function validateData(Request $request, $ignoreId = null): array
    {
        $bulanRules = ['nullable', 'integer', 'min:0'];

        $validated = $request->validate([
            'nama_produk'   => ['required', 'string', 'max:255'],
            'harga_satuan'  => ['required', 'integer', 'min:0'],
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
            'nama_produk'   => 'Nama Produk',
            'harga_satuan'  => 'Harga Satuan',
        ]);

        foreach (['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'] as $m) {
            $validated[$m] = (int) ($validated[$m] ?? 0);
        }

        return $validated;
    }

public function semestaU(Request $request)
    {
        $row = Penjualan::when($request->id, function ($q) use ($request) {
                return $q->where('id', (int)$request->id);
            })
            ->orderByDesc('id')
            ->first();

        if (!$row) {
            return redirect()->route('penjualan.index')
                ->with('error', 'Belum ada data penjualan. Silakan tambahkan data terlebih dahulu.');
        }

        [$series, $values] = $this->buildSeriesFromRow($row);

        $universe = $this->getOrCreateUniverse($row->nama_produk, $series, $values);
        $set      = $this->getOrCreateIntervalSet($universe, count($values));

        // Kumpulkan entitas terkait yang dibutuhkan view
        [$intervals, $fuzzySets, $fuzzis, $flrs, $flrg, $matrix, $cells, $forecasts, $mape, $future]
            = $this->gatherComputedData($set);

        // Build payload khusus chart agar view ringan
        $charts = $this->buildChartsPayload($series, $forecasts, $cells, $mape, $future);

        return view('admin.fts-semesta', [
            'produk'       => $row->nama_produk,
            'series'       => $series,
            'universe'     => $universe,
            'iset'         => $set,
            'intervals'    => $intervals,
            'fuzzySets'    => $fuzzySets,
            'fuzzis'       => $fuzzis,
            'flrs'         => $flrs,
            'flrg'         => $flrg,
            'markov'       => $matrix,
            'markovCells'  => $cells,
            'forecasts'    => $forecasts,
            'mape'         => $mape,
            'future'       => $future,
            'charts'       => $charts,
        ]);
    }
 /**
     * Pastikan Universe ada; jika belum, hitung dan buat.
     * @param string $produk
     * @param array<int,array{label:string,jumlah:int|float}> $series
     * @param array<int,int|float> $values
     */
    
    private function getOrCreateUniverse(string $produk, array $series, array $values): FtsUniverse
    {
        $universe = FtsUniverse::where('produk', $produk)
            ->whereNull('periode_mulai')
            ->whereNull('periode_selesai')
            ->first();

        if ($universe) {
            return $universe;
        }

        return $this->computeUniverse(
            produk: $produk,
            periodeMulai: null,
            periodeSelesai: null,
            values: $values,
            d1: 2,
            d2: 2,
            series: $series
        );
    }

    /**
     * Pastikan IntervalSet (Sturges) ada; jika belum, hitung dan buat.
     */
    private function getOrCreateIntervalSet(FtsUniverse $universe, int $n): FtsIntervalSet
    {
        $set = FtsIntervalSet::where('universe_id', $universe->id)
            ->where('method', 'sturges')
            ->first();

        if ($set) {
            return $set;
        }

        return $this->computeIntervalsFromUniverse($universe, $n);
    }

    /**
     * Ambil semua data turunan untuk view.
     * @return array{0:Collection,1:Collection,2:Collection,3:Collection,4:array<string,array<string,int>>,5:?FtsMarkovMatrix,6:Collection,7:Collection,8:?FtsMapeSummary,9:Collection}
     */
    private function gatherComputedData(FtsIntervalSet $set): array
    {
        /** @var Collection $intervals */
        $intervals = $set->intervals()->get();

        /** @var Collection $fuzzySets */
        $fuzzySets = FtsFuzzySet::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        /** @var Collection $fuzzis */
        $fuzzis = FtsFuzzification::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        /** @var Collection $flrs */
        $flrs = FtsFlr::where('interval_set_id', $set->id)
            ->orderBy('urut_from')->get();

        /** @var Collection $flrgRaw */
        $flrgRaw = FtsFlrgItem::where('interval_set_id', $set->id)
            ->orderBy('current_state')
            ->orderBy('next_state')
            ->get();

        // Bentuk associative current_state => [next_state => freq]
        $flrg = [];
        foreach ($flrgRaw as $it) {
            $cs = (string)$it->current_state;
            $ns = (string)$it->next_state;
            $flrg[$cs][$ns] = (int)$it->freq;
        }

        /** @var ?FtsMarkovMatrix $matrix */
        $matrix = FtsMarkovMatrix::where('interval_set_id', $set->id)->first();

        /** @var Collection $cells */
        $cells = $matrix
            ? $matrix->cells()->orderBy('row_state')->orderBy('col_state')->get()
            : collect();

        /** @var Collection $forecasts */
        $forecasts = FtsForecast::where('interval_set_id', $set->id)->orderBy('urut')->get();

        /** @var ?FtsMapeSummary $mape */
        $mape = FtsMapeSummary::where('interval_set_id', $set->id)->first();

        /** @var Collection $future */
        $future = FtsFutureForecast::where('interval_set_id', $set->id)->orderBy('seq')->get();

        return [$intervals, $fuzzySets, $fuzzis, $flrs, $flrg, $matrix, $cells, $forecasts, $mape, $future];
    }

    /**
     * Siapkan payload untuk ApexCharts agar view sederhana.
     * @param array<int,array{label:string,jumlah:int|float}> $series
     * @return array<string,mixed>
     */
    private function buildChartsPayload(array $series, Collection $forecasts, Collection $cells, ?FtsMapeSummary $mape, Collection $future): array
    {
        $labels = array_map(static fn($r) => (string)$r['label'], $series);
        $aktual = array_map(static fn($r) => (int)$r['jumlah'], $series);

        // Forecast line sejajar label (t=1 null)
        $byUrut  = $forecasts->keyBy('urut');
        $fcLine  = [];
        for ($i = 1, $N = count($labels); $i <= $N; $i++) {
            /** @var FtsForecast|null $row */
            $row = $byUrut->get($i);
            $fcLine[] = $row ? $row->f_final_round : null;
        }

        // APE (%) + MAPE
        $apeRows   = $forecasts->filter(static fn($r) => $r->urut >= 2 && $r->ape !== null);
        $apeLabels = array_values($apeRows->pluck('periode_label')->all());
        $apeValues = array_values($apeRows->map(static fn($r) => round(((float)$r->ape) * 100, 4))->all());
        $mapePct   = $mape ? (float)$mape->mape_pct : 0.0;

        // Heatmap Markov A1..A5
        $states = ['A1','A2','A3','A4','A5'];
        $heatmap = [];
        foreach ($states as $rs) {
            $rowData = [];
            foreach ($states as $cs) {
                /** @var FtsMarkovCell|null $cell */
                $cell = $cells->first(static function ($c) use ($rs, $cs) {
                    return $c->row_state === $rs && $c->col_state === $cs;
                });
                $rowData[] = ['x' => $cs, 'y' => $cell ? (float)$cell->prob : 0.0];
            }
            $heatmap[] = ['name' => $rs, 'data' => $rowData];
        }

        // Future 7
        $futureLabels = array_values($future->pluck('periode_label')->all());
        $futureVals   = array_values($future->pluck('f_round')->all());

        return [
            'aktual_vs_forecast' => [
                'labels'   => $labels,
                'aktual'   => $aktual,
                'forecast' => $fcLine,
            ],
            'error_mape' => [
                'labels' => $apeLabels,
                'ape'    => $apeValues,
                'mape'   => $mapePct,
            ],
            'markov_heatmap' => $heatmap,
            'future' => [
                'labels' => $futureLabels,
                'values' => $futureVals,
            ],
        ];
    }


    protected function buildSeriesFromRow(Penjualan $row): array
    {
        $fields = ['apr','mei','jun','jul','agu','sep','okt','nov','des','jan','feb','mar'];

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
                'produk'         => $produk,
                'periode_mulai'  => $periodeMulai,
                'periode_selesai'=> $periodeSelesai,
            ],
            [
                'n'          => $n,
                'dmin'       => $dmin,
                'dmax'       => $dmax,
                'd1'         => $d1,
                'd2'         => $d2,
                'u_min'      => $uMin,
                'u_max'      => $uMax,
                'input_series'=> $series,
            ]
        );
    }

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
        $k = max(1, (int) round(1 + 3.322 * log10(max(1, $N))));
        $range = $universe->u_max - $universe->u_min;
        $l = $k > 0 ? ($range / $k) : $range;

        $set = FtsIntervalSet::updateOrCreate(
            ['universe_id' => $universe->id, 'method' => 'sturges'],
            [
                'produk'      => $universe->produk,
                'n_period'    => $N,
                'k_interval'  => $k,
                'l_interval'  => $l,
                'u_min'       => $universe->u_min,
                'u_max'       => $universe->u_max,
            ]
        );

        FtsInterval::where('interval_set_id', $set->id)->delete();

        $uMin = (float) $universe->u_min;
        $uMax = (float) $universe->u_max;

        for ($i = 0; $i < $k; $i++) {
            $lower = $uMin + $i * $l;
            $upper = $uMin + ($i + 1) * $l;
            if ($i === $k - 1) {
                $upper = $uMax;
            }
            $mid = ($lower + $upper) / 2.0;

            FtsInterval::create([
                'interval_set_id' => $set->id,
                'kode'            => 'u' . ($i + 1),
                'urut'            => $i + 1,
                'lower_bound'     => $lower,
                'upper_bound'     => $upper,
                'mid_point'       => $mid,
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
                'kode'            => $r['kode'],
                'urut'            => $r['urut'],
                'mu_u1'           => $r['mu'][0],
                'mu_u2'           => $r['mu'][1],
                'mu_u3'           => $r['mu'][2],
                'mu_u4'           => $r['mu'][3],
                'mu_u5'           => $r['mu'][4],
            ]);
        }
    }

    protected function computeFuzzifications(
        FtsUniverse $universe,
        FtsIntervalSet $set,
        array $series,
        array $values
    ): void {
        FtsFuzzification::where('interval_set_id', $set->id)->delete();

        $intervals = $set->intervals()->get();
        $k = $intervals->count();

        foreach ($series as $i => $row) {
            $x = (float) $row['jumlah'];

            $chosen = null;
            foreach ($intervals as $idx => $iv) {
                $lower = (float) $iv->lower_bound;
                $upper = (float) $iv->upper_bound;

                $in = ($idx < $k - 1)
                    ? ($x >= $lower && $x < $upper)
                    : ($x >= $lower && $x <= $upper);

                if ($in) {
                    $chosen = $iv;
                    break;
                }
            }

            $fuzzyKode = $chosen ? ('A' . $chosen->urut) : null;
            $intKode = $chosen ? $chosen->kode : null;

            FtsFuzzification::create([
                'universe_id'    => $universe->id,
                'interval_set_id'=> $set->id,
                'produk'         => $universe->produk,
                'urut'           => $i + 1,
                'periode_label'  => $row['label'],
                'nilai'          => (int) $row['jumlah'],
                'interval_kode'  => $intKode,
                'fuzzy_kode'     => $fuzzyKode,
            ]);
        }
    }

    protected function computeFLR(FtsIntervalSet $set): void
    {
        FtsFlr::where('interval_set_id', $set->id)->delete();

        $rows = FtsFuzzification::where('interval_set_id', $set->id)
            ->orderBy('urut')->get();

        for ($i = 0; $i < $rows->count() - 1; $i++) {
            $cur = $rows[$i];
            $nxt = $rows[$i + 1];

            FtsFlr::create([
                'interval_set_id' => $set->id,
                'urut_from'       => $cur->urut,
                'urut_to'         => $nxt->urut,
                'periode_from'    => $cur->periode_label,
                'periode_to'      => $nxt->periode_label,
                'state_from'      => $cur->fuzzy_kode,
                'state_to'        => $nxt->fuzzy_kode,
            ]);
        }
    }

    protected function computeFLRG(FtsIntervalSet $set): void
    {
        FtsFlrgItem::where('interval_set_id', $set->id)->delete();

        $flrs = FtsFlr::where('interval_set_id', $set->id)->orderBy('urut_from')->get();

        $grouped = [];
        foreach ($flrs as $r) {
            $cs = $r->state_from;
            $ns = $r->state_to;
            if (!isset($grouped[$cs])) {
                $grouped[$cs] = [];
            }
            if (!isset($grouped[$cs][$ns])) {
                $grouped[$cs][$ns] = 0;
            }
            $grouped[$cs][$ns] += 1;
        }

        foreach ($grouped as $cs => $nexts) {
            foreach ($nexts as $ns => $freq) {
                FtsFlrgItem::create([
                    'interval_set_id' => $set->id,
                    'current_state'   => $cs,
                    'next_state'      => $ns,
                    'freq'            => $freq,
                ]);
            }
        }
    }

    protected function computeMarkovMatrix(FtsIntervalSet $set): FtsMarkovMatrix
    {
        $items = FtsFlrgItem::where('interval_set_id', $set->id)->get();

        $k = (int) $set->k_interval;
        $states = [];
        for ($i = 1; $i <= $k; $i++) {
            $states[] = 'A' . $i;
        }

        $rowTotals = array_fill_keys($states, 0);
        foreach ($items as $it) {
            $rowTotals[$it->current_state] += (int) $it->freq;
        }

        $matrix = FtsMarkovMatrix::updateOrCreate(
            ['interval_set_id' => $set->id],
            ['k_state' => $k]
        );

        FtsMarkovCell::where('matrix_id', $matrix->id)->delete();

        foreach ($states as $rs) {
            $den = max(0, (int) ($rowTotals[$rs] ?? 0));
            foreach ($states as $cs) {
                $freq = (int) ($items->firstWhere(fn($x) => $x->current_state === $rs && $x->next_state === $cs)->freq ?? 0);
                $prob = $den > 0 ? $freq / $den : 0.0;

                FtsMarkovCell::create([
                    'matrix_id'  => $matrix->id,
                    'row_state'  => $rs,
                    'col_state'  => $cs,
                    'freq'       => $freq,
                    'row_total'  => $den,
                    'prob'       => $prob,
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

        $m = [];
        foreach ($intervals as $i => $iv) {
            $m[$i + 1] = (float) $iv->mid_point;
        }

        $cells = FtsMarkovCell::where('matrix_id', $matrix->id)->get();
        $P = [];
        foreach ($cells as $c) {
            $P[$c->row_state][$c->col_state] = (float) $c->prob;
        }

        FtsForecast::where('interval_set_id', $set->id)->delete();

        if ($fuzzis->count() === 0) {
            return;
        }

        $first = $fuzzis[0];
        FtsForecast::create([
            'universe_id'    => $universe->id,
            'interval_set_id'=> $set->id,
            'matrix_id'      => $matrix->id,
            'produk'         => $universe->produk,
            'urut'           => $first->urut,
            'periode_label'  => $first->periode_label,
            'y_actual'       => (int) $first->nilai,
            'y_prev'         => null,
            'prev_state'     => null,
            'curr_state'     => null,
            'next_state'     => null,
            'p1'             => 0,
            'p2'             => 0,
            'p3'             => 0,
            'p4'             => 0,
            'p5'             => 0,
            'f_value'        => null,
            'dt'             => null,
        ]);

        $halfL = (float) $set->l_interval / 2.0;

        for ($i = 1; $i < $fuzzis->count(); $i++) {
            $prev = $fuzzis[$i - 1];
            $curr = $fuzzis[$i];

            $rowState = $prev->fuzzy_kode;
            $yPrev = (float) $prev->nilai;

            $p = [
                1 => (float) ($P[$rowState]['A1'] ?? 0.0),
                2 => (float) ($P[$rowState]['A2'] ?? 0.0),
                3 => (float) ($P[$rowState]['A3'] ?? 0.0),
                4 => (float) ($P[$rowState]['A4'] ?? 0.0),
                5 => (float) ($P[$rowState]['A5'] ?? 0.0),
            ];

            $iState = (int) str_replace('A', '', $rowState);
            $jState = (int) str_replace('A', '', $curr->fuzzy_kode);

            $F = 0.0;
            for ($j = 1; $j <= 5; $j++) {
                $weight = $p[$j];
                if ($weight == 0) {
                    continue;
                }
                $val = ($j === $iState) ? $yPrev : $m[$j];
                $F += $val * $weight;
            }

            $deltaIdx = $jState - $iState;
            $Dt = $deltaIdx * $halfL;

            $FprimeDec = round($F + $Dt, 2);
            $FprimeInt = (int) round($FprimeDec, 0, PHP_ROUND_HALF_UP);

            FtsForecast::create([
                'universe_id'     => $universe->id,
                'interval_set_id' => $set->id,
                'matrix_id'       => $matrix->id,
                'produk'          => $universe->produk,
                'urut'            => $curr->urut,
                'periode_label'   => $curr->periode_label,
                'y_actual'        => (int) $curr->nilai,
                'y_prev'          => (int) $yPrev,
                'prev_state'      => $rowState,
                'curr_state'      => $rowState,
                'next_state'      => $curr->fuzzy_kode,
                'p1'              => $p[1],
                'p2'              => $p[2],
                'p3'              => $p[3],
                'p4'              => $p[4],
                'p5'              => $p[5],
                'f_value'         => round($F, 2),
                'dt'              => round($Dt, 1),
                'f_final'         => $FprimeDec,
                'f_final_round'   => $FprimeInt,
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
            if ($r->f_final_round !== null && $r->urut >= 2 && $r->y_actual > 0) {
                $ape = abs(($r->y_actual - $r->f_final_round) / $r->y_actual);
                $r->ape = round($ape, 5);
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
                'n_rows'   => $cnt,
                'sum_ape'  => round($sum, 6),
                'mape_pct' => round($mapePct, 4),
            ]
        );
    }

    protected function computeFuture7(
        FtsUniverse $universe,
        FtsIntervalSet $set,
        FtsMarkovMatrix $matrix
    ): void {
        FtsFutureForecast::where('interval_set_id', $set->id)->delete();

        $intervals = $set->intervals()->orderBy('urut')->get();
        $m = [];
        foreach ($intervals as $i => $iv) {
            $m[$i + 1] = (float) $iv->mid_point;
        }

        $cells = FtsMarkovCell::where('matrix_id', $matrix->id)->get();
        $p = [
            1 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A1')->prob ?? 0),
            2 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A2')->prob ?? 0),
            3 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A3')->prob ?? 0),
            4 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A4')->prob ?? 0),
            5 => (float) ($cells->firstWhere(fn($x) => $x->row_state === 'A2' && $x->col_state === 'A5')->prob ?? 0),
        ];

        $last = FtsForecast::where('interval_set_id', $set->id)->orderByDesc('urut')->first();
        $yIn = (float) ($last?->f_final ?? 0.0);

        $labels = ['April 2025','Mei 2025','Juni 2025','Juli 2025','Agustus 2025','September 2025','Oktober 2025'];

        for ($seq = 1; $seq <= 7; $seq++) {
            $F = $m[1] * $p[1] + $yIn * $p[2] + $m[3] * $p[3] + $m[4] * $p[4] + $m[5] * $p[5];
            $Fdec = round($F, 2);
            $Fround = (int) round($Fdec, 0, PHP_ROUND_HALF_UP);

            FtsFutureForecast::create([
                'interval_set_id' => $set->id,
                'matrix_id'       => $matrix->id,
                'produk'          => $universe->produk,
                'start_state'     => 'A2',
                'seq'             => $seq,
                'periode_label'   => $labels[$seq - 1],
                'y_input'         => round($yIn, 2),
                'p1'              => $p[1],
                'p2'              => $p[2],
                'p3'              => $p[3],
                'p4'              => $p[4],
                'p5'              => $p[5],
                'f_value'         => $Fdec,
                'f_round'         => $Fround,
            ]);

            $yIn = $Fdec;
        }
    }
}
