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
                        <span class="text">FTS — Himpunan Semesta (U)</span>
                    </div>
                    <a href="{{ route('penjualan.index') }}" class="btn btn-outline-secondary btn-sm">
                        &larr; Kembali ke Data Penjualan
                    </a>
                </div>

                {{-- Tabel Input (12 Bulan) --}}
                <div class="card mb-4">
                    <div class="card-header fw-bold">Data Penjualan — {{ $produk }}</div>
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
                                            <td class="text-end">{{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                    <tr class="table-light">
                                        <td colspan="2" class="text-end fw-bold">Total n</td>
                                        <td class="text-end fw-bold">{{ count($series) }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <small class="text-muted">Periode: {{ optional($universe->periode_mulai)->translatedFormat('F Y') }}
                            – {{ optional($universe->periode_selesai)->translatedFormat('F Y') }}</small>
                    </div>
                </div>

                {{-- Tabel Ringkasan Semesta U --}}
                <div class="card">
                    <div class="card-header fw-bold">Ringkasan Perhitungan Semesta (U)</div>
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
                    <div class="card-header fw-bold">Penentuan Jumlah & Panjang Interval</div>
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
                                        <td>{{ $iset->k_interval }} <small class="text-muted">(dibulatkan dari 1 + 3,322
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

                {{-- TABEL INTERVAL LINGUISTIK --}}
                <div class="card mt-3">
                    <div class="card-header fw-bold">Interval Linguistik & Nilai Tengah</div>
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
                            Catatan: Interval terakhir dipastikan berakhir tepat di U<sub>max</sub> untuk menghindari error
                            pembulatan.
                        </small>
                    </div>
                </div>


            </div>
        </div>
    </section>
@endsection
