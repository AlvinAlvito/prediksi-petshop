@extends('layouts.main')

@section('content')
    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>

            <div class="search-box">
                <i class="uil uil-search"></i>
                <input type="text" placeholder="Search here..." disabled>
            </div>

            <img src="/images/profil.png" alt="">
        </div>

        <div class="dash-content">
            <div class="activity">
                <div class="title d-flex align-items-center justify-content-between">
                    <div>
                        <i class="uil uil-clipboard-notes"></i>
                        <span class="text">Hasil Dan Proses</span>
                    </div>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary btn-sm">
                        &larr; Kembali ke Data Penjualan
                    </a>
                </div>

                <div class="row">
                    <div class="col-6">
                        {{-- Tabel Input (12 Bulan) --}}
                        <div class="card mb-4">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Data Penjualan</span> —
                                {{ $produk }}</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:60px;">No</th>
                                                <th>Bulan</th>
                                                <th class="text-end">Jumlah</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($series as $i => $row)
                                                <tr>
                                                    <td>{{ $i + 1 }}</td>
                                                    <td>{{ $row['label'] }}</td>
                                                    <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                            <tr class="table-light">
                                                <td colspan="2" class="text-end fw-bold">Total n</td>
                                                <td class="text-end fw-bold">{{ count($series) }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">Periode:
                                    {{ optional($universe->periode_mulai)->translatedFormat('F Y') }}
                                    – {{ optional($universe->periode_selesai)->translatedFormat('F Y') }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        {{-- Tabel Ringkasan Semesta U --}}
                        <div class="card">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 1</span> Ringkasan
                                Perhitungan Semesta (U)</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <tbody>
                                            <tr>
                                                <th style="width:260px;">Banyak data (n)</th>
                                                <td>{{ $universe->n }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dmin</th>
                                                <td>{{ number_format($universe->dmin, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Dmax</th>
                                                <td>{{ number_format($universe->dmax, 0, ',', '.') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Nilai D1 &amp; D2</th>
                                                <td>{{ $universe->d1 }} &amp; {{ $universe->d2 }}</td>
                                            </tr>
                                            <tr class="table-light">
                                                <th>Himpunan Semesta (U) = [Dmin − D1, Dmax + D2]</th>
                                                <td>[ {{ $universe->u_min }}, {{ $universe->u_max }} ]</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="mt-2">
                                    <code>U = [{{ $universe->dmin }} − {{ $universe->d1 }}, {{ $universe->dmax }} +
                                        {{ $universe->d2 }}] = [{{ $universe->u_min }}, {{ $universe->u_max }}]</code>
                                </div>
                            </div>
                        </div>
                        {{-- RINGKASAN STURGES & PANJANG INTERVAL --}}
                        <div class="card mt-4">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 2</span> Penentuan
                                Jumlah & Panjang Interval</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <tbody>
                                            <tr>
                                                <th style="width:260px;">Metode</th>
                                                <td>Sturges</td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Periode (N)</th>
                                                <td>{{ $iset->n_period ?? count($series) }}</td>
                                            </tr>
                                            <tr>
                                                <th>Jumlah Interval (k)</th>
                                                <td>{{ $iset->k_interval }} <small class="text-muted">(dibulatkan dari 1 +
                                                        3,322
                                                        log₁₀(N))</small></td>
                                            </tr>
                                            <tr>
                                                <th>Panjang Interval (l)</th>
                                                <td>{{ number_format($iset->l_interval, 1, ',', '.') }}</td>
                                            </tr>
                                            <tr class="table-light">
                                                <th>Rumus</th>
                                                <td>
                                                    <code>k = 1 + 3,322&times;log₁₀(N)</code>,
                                                    <code>l = (U<sub>max</sub> - U<sub>min</sub>) / k</code>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        {{-- TABEL INTERVAL LINGUISTIK --}}
                        <div class="card mt-3">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 3</span> Interval
                                Linguistik & Nilai Tengah</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:80px;">ui</th>
                                                <th class="text-end">Batas Bawah</th>
                                                <th class="text-end">Batas Atas</th>
                                                <th class="text-end">Nilai Tengah (mi)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($intervals as $iv)
                                                <tr>
                                                    <td>{{ strtoupper($iv->kode) }}</td>
                                                    <td class="text-end">
                                                        {{ rtrim(rtrim(number_format($iv->lower_bound, 1, ',', '.'), '0'), ',') }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ rtrim(rtrim(number_format($iv->upper_bound, 1, ',', '.'), '0'), ',') }}
                                                    </td>
                                                    <td class="text-end">
                                                        {{ rtrim(rtrim(number_format($iv->mid_point, 1, ',', '.'), '0'), ',') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">
                                    Catatan: Interval terakhir dipastikan berakhir tepat di U<sub>max</sub> untuk
                                    menghindari error
                                    pembulatan.
                                </small>
                            </div>
                        </div>


                    </div>
                    <div class="col-6">
                        {{-- HIMPUNAN FUZZY A1..A5 --}}
                        <div class="card mt-4">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 4</span>
                                Himpunan Fuzzy (A1
                                s/d A{{ $iset->k_interval }})</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:80px;">Ai</th>
                                                @for ($j = 1; $j <= $iset->k_interval; $j++)
                                                    <th class="text-center">μ/u{{ $j }}</th>
                                                @endfor
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($fuzzySets as $fs)
                                                <tr>
                                                    <td>{{ $fs->kode }}</td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim(number_format($fs->mu_u1, 2, ',', '.'), '0'), ',') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim(number_format($fs->mu_u2, 2, ',', '.'), '0'), ',') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim(number_format($fs->mu_u3, 2, ',', '.'), '0'), ',') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim(number_format($fs->mu_u4, 2, ',', '.'), '0'), ',') }}
                                                    </td>
                                                    <td class="text-center">
                                                        {{ rtrim(rtrim(number_format($fs->mu_u5, 2, ',', '.'), '0'), ',') }}
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-6">
                        {{-- DATA HASIL FUZZYFIKASI --}}
                        <div class="card mt-3">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 5</span> Data Hasil
                                Fuzzyfikasi — {{ $produk }}</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:60px;">No</th>
                                                <th>Periode</th>
                                                <th class="text-end">Jumlah</th>
                                                <th>Fuzzyfikasi</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($fuzzis as $fz)
                                                <tr>
                                                    <td>{{ $fz->urut }}</td>
                                                    <td>{{ $fz->periode_label }}</td>
                                                    <td class="text-end">{{ number_format($fz->nilai, 0, ',', '.') }}</td>
                                                    <td>{{ $fz->fuzzy_kode }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">Penentuan Aᵢ mengikuti interval tempat nilai berada (uⱼ →
                                    Aⱼ).</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
                        {{-- FLR: Fuzzy Logical Relationship --}}
                        <div class="card mt-4">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 6</span> Fuzzy
                                Logical Relationship (FLR)</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-striped align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Periode</th>
                                                <th>FLR</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($flrs as $r)
                                                <tr>
                                                    <td>{{ Str::limit($r->periode_from, 12, '') }} &rarr;
                                                        {{ Str::limit($r->periode_to, 12, '') }}</td>
                                                    <td>{{ $r->state_from }} &rarr; {{ $r->state_to }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">FLR dibentuk dari pasangan berurutan: (periode i) → (periode
                                    i+1).</small>
                            </div>
                        </div>

                    </div>
                    <div class="col-12">
                        {{-- FLRG: Fuzzy Logical Relationship Group --}}
                        <div class="card mt-3">
                            <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 7</span> Fuzzy
                                Logical Relationship Group (FLRG)</div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered align-middle">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width:80px;">No</th>
                                                <th style="width:140px;">Current State</th>
                                                <th>Next State</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $no = 1; @endphp
                                            @foreach (['A1', 'A2', 'A3', 'A4', 'A5'] as $state)
                                                <tr>
                                                    <td>{{ $no++ }}</td>
                                                    <td>{{ $state }}</td>
                                                    <td>
                                                        @php
                                                            $items = $flrg[$state] ?? [];
                                                            // format: 2(A1), A1, A2, dst
                                                            $parts = [];
                                                            foreach ($items as $ns => $freq) {
                                                                $parts[] = $freq > 1 ? $freq . '(' . $ns . ')' : $ns;
                                                            }
                                                            echo $parts ? implode(', ', $parts) : '-';
                                                        @endphp
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <small class="text-muted">FLRG adalah pengelompokan semua FLR dengan state awal yang sama,
                                    beserta
                                    frekuensi kemunculan state berikutnya.</small>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        {{-- MARKOV TRANSITION PROBABILITY MATRIX (R) --}}
                        @if ($markov)
                            <div class="col-6">
                                <div class="card mt-4">
                                <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 8</span>
                                    Matriks Probabilitas Transisi Markov (R) — Pecahan</div>
                                <div class="card-body">
                                    @php
                                        $states = [];
                                        for ($i = 1; $i <= ($iset->k_interval ?? 0); $i++) {
                                            $states[] = 'A' . $i;
                                        }

                                        // Bentuk grid [row_state][col_state] => cell
                                        $grid = [];
                                        foreach ($markovCells as $c) {
                                            $grid[$c->row_state][$c->col_state] = $c;
                                        }
                                    @endphp

                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle text-center">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>R</th>
                                                    @foreach ($states as $cs)
                                                        <th>{{ $cs }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($states as $rs)
                                                    <tr>
                                                        <th class="table-light">{{ $rs }}</th>
                                                        @foreach ($states as $cs)
                                                            @php
                                                                $cell = $grid[$rs][$cs] ?? null;
                                                                $num = $cell?->freq ?? 0;
                                                                $den = $cell?->row_total ?? 0;
                                                                $txt = $den > 0 ? $num . ' / ' . $den : '0';
                                                            @endphp
                                                            <td>{{ $txt }}</td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">Baris tanpa transisi keluar (total=0) ditampilkan sebagai
                                        0.</small>
                                </div>
                            </div>
                            </div>

                           <div class="col-6">
                             <div class="card mt-3">
                                <div class="card-header fw-bold">Matriks Probabilitas Transisi Markov (R) — Desimal</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle text-center">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>R</th>
                                                    @foreach ($states as $cs)
                                                        <th>{{ $cs }}</th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($states as $rs)
                                                    <tr>
                                                        <th class="table-light">{{ $rs }}</th>
                                                        @foreach ($states as $cs)
                                                            @php
                                                                $cell = $grid[$rs][$cs] ?? null;
                                                                $p = $cell?->prob ?? 0;
                                                            @endphp
                                                            <td>{{ rtrim(rtrim(number_format($p, 2, '.', ''), '0'), '.') }}
                                                            </td>
                                                        @endforeach
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">Nilai desimal dibulatkan 2 angka seperti contoh (0.5, 0.25,
                                        dst).</small>
                                </div>
                            </div>
                           </div>
                        @endif

                    </div>

                    <div class="col-12">
                        {{-- HASIL PERAMALAN AWAL F(t) --}}
                        @if (!empty($forecasts) && count($forecasts) > 0)
                            <div class="card mt-4">
                                <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 9</span> Hasil Peramalan Awal F(t)</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th >Periode</th>
                                                    <th class="text-end">Data Aktual Y(t)</th>
                                                    <th class="text-end">Peramalan Awal F(t)</th>
                                                    <th class="text-center">State (t−1)</th>
                                                    <th class="text-end">Y(t−1)</th>
                                                    <th class="text-center">P(baris)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($forecasts as $fc)
                                                    <tr>
                                                        <td>{{ $fc->periode_label }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($fc->y_actual, 0, ',', '.') }}</td>
                                                        <td class="text-end">
                                                            {{ $fc->f_value !== null ? number_format($fc->f_value, 2, '.', '') : '-' }}
                                                        </td>
                                                        <td class="text-center">{{ $fc->prev_state ?? '-' }}</td>
                                                        <td class="text-end">
                                                            {{ $fc->y_prev !== null ? number_format($fc->y_prev, 0, ',', '.') : '-' }}
                                                        </td>
                                                        <td class="text-center">
                                                            @if ($fc->prev_state)
                                                                [{{ rtrim(rtrim(number_format($fc->p1, 2, '.', ''), '0'), '.') }},
                                                                {{ rtrim(rtrim(number_format($fc->p2, 2, '.', ''), '0'), '.') }},
                                                                {{ rtrim(rtrim(number_format($fc->p3, 2, '.', ''), '0'), '.') }},
                                                                {{ rtrim(rtrim(number_format($fc->p4, 2, '.', ''), '0'), '.') }},
                                                                {{ rtrim(rtrim(number_format($fc->p5, 2, '.', ''), '0'), '.') }}]
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                    <small class="text-muted">
                                        Rumus: untuk baris state A<sub>i</sub> (bulan t−1), F(t) = Y(t−1)·P<sub>ii</sub> +
                                        ∑<sub>j≠i</sub> m<sub>j</sub>·P<sub>ij</sub>.
                                        Nilai m<sub>j</sub> diambil dari mid-point tiap interval u<sub>j</sub>.
                                    </small>
                                </div>
                            </div>
                        @endif

                    </div>

                </div>











            </div>
        </div>
    </section>
@endsection
