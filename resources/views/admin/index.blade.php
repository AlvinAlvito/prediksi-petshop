@extends('layouts.main')
@section('content')
    <section class="dashboard">
        <div class="top">
            <i class="uil uil-bars sidebar-toggle"></i>
            <div class="search-box">
                <i class="uil uil-search"></i>
                <input type="text" placeholder="Search here...">
            </div>
            <img src="/images/profil.png" alt="">
        </div>

        <div class="dash-content">
            <div class="overview">
                <div class="title">
                    <i class="uil uil-chart-line"></i>
                    <span class="text">Dashboard Penjualan</span>
                </div>
            </div>
            <div class="boxes">
                <div class="box box1">
                    <i class="uil uil-thumbs-up"></i>
                    <span class="text">Total Produk</span>
                    <span class="number">123</span>
                </div>
                <div class="box box2">
                    <i class="uil uil-comments"></i>
                    <span class="text">Total Pegawai</span>
                    <span class="number">6</span>
                </div>
                <div class="box box3">
                    <i class="uil uil-share"></i>
                    <span class="text">Total Penjualan</span>
                    <span class="number">1606</span>
                </div>
            </div>

            {{-- Grafik Chart Apex --}}
            <div class="activity">
                <div class="title mb-3">
                    <i class="uil uil-chart-bar"></i>
                    <span class="text">Analisis dan Prediksi</span>
                </div>

                <div class="row">
                    {{-- Chart 1: Total Penjualan per Produk --}}
                    <div class="col-md-6 mb-4">
                        <div id="chartTotalProduk"></div>
                    </div>


                    {{-- Chart 3: MAPE tiap produk --}}
                    <div class="col-md-6 mb-4">
                        <div id="chartMape"></div>
                    </div>

                    {{-- Chart 4: Total penjualan per bulan --}}
                    <div class="col-md-6 mb-4">
                        <div id="chartTotalBulan"></div>
                    </div>

                    {{-- Chart 5: Top 5 Produk Terlaris --}}
                    <div class="col-md-6 mb-4">
                        <div id="chartTopProduk"></div>
                    </div>

                    {{-- Chart 6: Data Aktual vs Prediksi Satu Produk (ambil pertama saja) --}}
                    <div class="col-md-6 mb-4">
                        <div id="chartBandingAktual"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ApexCharts CDN --}}
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>


@endsection
