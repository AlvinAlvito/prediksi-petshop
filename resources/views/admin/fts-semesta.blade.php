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
                <div class="d-flex align-items-center justify-content-between">
                    <div class="title">
                        <i class="uil uil-clipboard-notes"></i>
                        <span class="text">Hasil & Proses</span>
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
                                    <div class="card-header fw-bold">Matriks Probabilitas Transisi Markov (R) — Desimal
                                    </div>
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
                                        <small class="text-muted">Nilai desimal dibulatkan 2 angka seperti contoh (0.5,
                                            0.25,
                                            dst).</small>
                                    </div>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="row">
                        <div class="col-6">
                            {{-- HASIL PERAMALAN AWAL F(t) --}}
                            @if (!empty($forecasts) && count($forecasts) > 0)
                                <div class="card mt-4">
                                    <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 9</span>
                                        Hasil Peramalan Awal F(t)</div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>Periode</th>
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
                                            Rumus: untuk baris state A<sub>i</sub> (bulan t−1), F(t) = Y(t−1)·P<sub>ii</sub>
                                            +
                                            ∑<sub>j≠i</sub> m<sub>j</sub>·P<sub>ij</sub>.
                                            Nilai m<sub>j</sub> diambil dari mid-point tiap interval u<sub>j</sub>.
                                        </small>
                                    </div>
                                </div>
                            @endif

                        </div>
                        <div class="col-6">
                            {{-- NILAI PENYESUAIAN (Dt) --}}
                            @if (!empty($forecasts) && count($forecasts) > 0)
                                <div class="card mt-3">
                                    <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 10</span>
                                        Nilai Penyesuaian (Dt) pada Hasil Peramalan</div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-bordered align-middle">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th style="width:80px;">Periode (t)</th>
                                                        <th class="text-center">Current state</th>
                                                        <th class="text-center">Next state</th>
                                                        <th class="text-end">Data Aktual Y(t)</th>
                                                        <th class="text-end">Peramalan awal F(t)</th>
                                                        <th class="text-end">Dt</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($forecasts as $i => $fc)
                                                        <tr>
                                                            <td>{{ $fc->periode_label }}</td>
                                                            <td class="text-center">{{ $fc->curr_state ?? '-' }}</td>
                                                            <td class="text-center">{{ $fc->next_state ?? '-' }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($fc->y_actual, 0, ',', '.') }}</td>
                                                            <td class="text-end">
                                                                {{ $fc->f_value !== null ? number_format($fc->f_value, 2, '.', '') : '-' }}
                                                            </td>
                                                            <td class="text-end">
                                                                @if ($fc->dt === null)
                                                                    -
                                                                @else
                                                                    {{ number_format($fc->dt, 1, '.', '') }}
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                        <small class="text-muted">
                                            Aturan: Dt = (index(A<sub>next</sub>) − index(A<sub>current</sub>)) × (l/2).
                                            Dengan l = {{ number_format($iset->l_interval, 1, ',', '.') }}, maka l/2 =
                                            {{ number_format($iset->l_interval / 2, 1, ',', '.') }}.
                                        </small>
                                    </div>
                                </div>
                            @endif

                        </div>
                    </div>
                    <div class="col-6">
                        {{-- HASIL PERAMALAN AKHIR (F′(t) = F(t) + Dt) --}}
                        @if (!empty($forecasts) && count($forecasts) > 0)
                            <div class="card mt-3">
                                <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 11</span>
                                    Hasil Peramalan Akhir</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:80px;">No</th>
                                                    <th style="width:120px;">Periode</th>
                                                    <th class="text-end">Data Aktual Y(t)</th>
                                                    <th class="text-end">Peramalan Awal F(t)</th>
                                                    <th class="text-end">Dt</th>
                                                    <th class="text-end">Peramalan Akhir F′(t)</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($forecasts as $i => $fc)
                                                    <tr>
                                                        <td>{{ $i + 1 }}</td>
                                                        <td>{{ \Illuminate\Support\Str::of($fc->periode_label)->replace(' ', '-')->replace('2024', '2024')->replace('2025', '2025') }}
                                                        </td>
                                                        <td class="text-end">
                                                            {{ number_format($fc->y_actual, 0, ',', '.') }}</td>
                                                        <td class="text-end">
                                                            {{ $fc->f_value !== null ? number_format($fc->f_value, 2, '.', '') : '-' }}
                                                        </td>
                                                        <td class="text-end">
                                                            @if ($fc->dt === null)
                                                                -
                                                            @else
                                                                {{ number_format($fc->dt, 1, '.', '') }}
                                                            @endif
                                                        </td>
                                                        <td class="text-end">
                                                            @if ($fc->f_final === null)
                                                                -
                                                            @else
                                                                {{-- tampilkan bulat (≈) sesuai contoh --}}
                                                                {{ number_format($fc->f_final_round, 0, ',', '.') }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">
                                        F′(t) = F(t) + Dt, kemudian dibulatkan ke bilangan bulat terdekat (contoh: 212,50 →
                                        213).
                                    </small>
                                </div>
                            </div>
                        @endif

                    </div>

                    <div class="col-6">
                        {{-- PROSES 12: PERHITUNGAN MAPE --}}
                        @if (!empty($forecasts) && count($forecasts) > 1)
                            <div class="card mt-4">
                                <div class="card-header fw-bold"><span class="btn btn-primary btn-sm">Proses 12</span>
                                    Perhitungan MAPE (In-sample)</div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th class="text-end">Data Aktual Y(t)</th>
                                                    <th class="text-end">Peramalan Akhir F′(t)</th>
                                                    <th class="text-end">| (Y − F′) / Y |</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($forecasts as $r)
                                                    @if ($r->urut >= 2)
                                                        <tr>
                                                            <td class="text-end">
                                                                {{ number_format($r->y_actual, 0, ',', '.') }}</td>
                                                            <td class="text-end">
                                                                {{ number_format($r->f_final_round ?? 0, 0, ',', '.') }}
                                                            </td>
                                                            <td class="text-end">
                                                                {{ $r->ape !== null ? number_format($r->ape, 4, '.', '') : '-' }}
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                            @if ($mape)
                                                <tfoot>
                                                    <tr class="table-light">
                                                        <th colspan="2" class="text-end">MAPE</th>
                                                        <th class="text-end">
                                                            {{ number_format($mape->mape_pct, 2, ',', '.') }}%
                                                        </th>
                                                    </tr>
                                                </tfoot>
                                            @endif
                                        </table>
                                    </div>
                                    @if ($mape)
                                        <small class="text-muted">
                                            MAPE = (Σ APE) / {{ $mape->n_rows }} × 100% =
                                            {{ rtrim(rtrim(number_format($mape->sum_ape, 4, '.', ''), '0'), '.') }} /
                                            {{ $mape->n_rows }} × 100%
                                            = {{ number_format($mape->mape_pct, 4, '.', '') }}%.
                                        </small>
                                    @endif
                                </div>
                            </div>
                        @endif

                    </div>
                    <div class="col-12">
                        {{-- HASIL AKHIR: PERAMALAN 7 PERIODE KE DEPAN (Apr–Okt 2025) --}}
                        @if (!empty($future) && count($future) > 0)
                            <div class="card mt-3">
                                <div class="card-header fw-bold">
                                    <span class="btn btn-primary btn-sm">Hasil Akhir</span> Peramalan 7 Periode ke Depan
                                    (State Saat Ini: A2)
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-striped align-middle">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width:80px;">No</th>
                                                    <th style="width:140px;">Periode</th>
                                                    <th class="text-end">Hasil Peramalan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($future as $row)
                                                    <tr>
                                                        <td>{{ $row->seq }}</td>
                                                        <td>{{ $row->periode_label }}</td>
                                                        <td class="text-end">
                                                            {{ number_format($row->f_round, 0, ',', '.') }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <small class="text-muted">
                                        Rumus tiap periode menggunakan baris probabilitas state A2:
                                        F = m₁·P₂₁ + Y<sub>sebelumnya</sub>·P₂₂ + m₃·P₂₃ + m₄·P₂₄ + m₅·P₂₅.
                                        Nilai Y untuk langkah pertama adalah F′(12) desimal, dan untuk langkah berikutnya
                                        adalah hasil desimal periode sebelumnya.
                                    </small>
                                </div>
                            </div>
                        @endif

                    </div>

                    {{-- Grafik Chart Apex --}}
                    <div class="activity">
                        <div class="title mb-3">
                            <i class="uil uil-chart-bar"></i>
                            <span class="text">Analisis dan Prediksi</span>
                        </div>

                        <div class="row">
                            {{-- Chart 1: Aktual vs Forecast (Line) --}}
                            <div class="col-md-12 mb-4">
                                <h6 class="fw-bold text-center mb-2">Data Aktual vs Peramalan</h6>
                                <div id="chartAktualForecast"></div>
                            </div>

                            {{-- Chart 2: APE per Periode + garis MAPE (Bar + Annotation) --}}
                            <div class="col-md-6 mb-4">
                                <h6 class="fw-bold text-center mb-2">Error APE (%) per Periode & Garis MAPE</h6>
                                <div id="chartAPE"></div>
                            </div>

                            {{-- Chart 3: Heatmap Probabilitas Transisi Markov --}}
                            <div class="col-md-6 mb-4">
                                <h6 class="fw-bold text-center mb-2">Probabilitas Transisi Markov (Heatmap)</h6>
                                <div id="chartMarkovHeatmap"></div>
                            </div>

                            {{-- Chart 4: Forecast 7 Periode ke Depan (Line) --}}
                            <div class="col-md-12 mb-4">
                                <h6 class="fw-bold text-center mb-2">Peramalan 7 Periode ke Depan</h6>
                                <div id="chartFuture7"></div>
                            </div>
                        </div>
                    </div>






                </div>











            </div>
        </div>
    </section>

    {{-- ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        const CH = @json($charts);

        // Contoh: Aktual vs Forecast
        new ApexCharts(document.querySelector("#chartAktualForecast"), {
            chart: {
                type: 'line',
                height: 360
            },
            series: [{
                    name: "Aktual",
                    data: CH.aktual_vs_forecast.aktual
                },
                {
                    name: "Forecast",
                    data: CH.aktual_vs_forecast.forecast
                },
            ],
            xaxis: {
                categories: CH.aktual_vs_forecast.labels
            }
        }).render();

        // APE + MAPE
        new ApexCharts(document.querySelector("#chartAPE"), {
            chart: {
                type: 'bar',
                height: 360
            },
            series: [{
                name: "APE (%)",
                data: CH.error_mape.ape
            }],
            xaxis: {
                categories: CH.error_mape.labels
            },
            annotations: {
                yaxis: [{
                    y: CH.error_mape.mape,
                    borderColor: '#FF4560',
                    strokeDashArray: 4,
                    label: {
                        text: 'MAPE ' + CH.error_mape.mape.toFixed(2) + '%',
                        style: {
                            color: '#fff',
                            background: '#FF4560'
                        }
                    }
                }]
            }
        }).render();

        // Heatmap Markov
        new ApexCharts(document.querySelector("#chartMarkovHeatmap"), {
            chart: {
                type: 'heatmap',
                height: 360
            },
            series: CH.markov_heatmap,
            dataLabels: {
                enabled: true,
                formatter: v => (v ?? 0).toFixed(2)
            },
        }).render();

        // Future 7
        new ApexCharts(document.querySelector("#chartFuture7"), {
            chart: {
                type: 'line',
                height: 360
            },
            series: [{
                name: "Forecast",
                data: CH.future.values
            }],
            xaxis: {
                categories: CH.future.labels
            }
        }).render();
    </script>


@endsection
