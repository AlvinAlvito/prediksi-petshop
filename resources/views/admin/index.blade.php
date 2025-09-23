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

        {{-- KPI Cards --}}
        <div class="boxes">
            <div class="box box1">
                <i class="uil uil-package"></i>
                <span class="text">Total Produk</span>
                <span class="number">{{ $totalProduk }}</span>
            </div>
            <div class="box box2">
                <i class="uil uil-users-alt"></i>
                <span class="text">Total Pegawai</span>
                <span class="number">{{ $totalPegawai }}</span>
            </div>
            <div class="box box3">
                <i class="uil uil-shopping-bag"></i>
                <span class="text">Total Penjualan</span>
                <span class="number">{{ number_format($totalPenjualan, 0, ',', '.') }}</span>
            </div>
        </div>

        {{-- Charts --}}
        <div class="activity">
            <div class="title mb-3">
                <i class="uil uil-chart-bar"></i>
                <span class="text">Analisis & Prediksi</span>
            </div>

            <div class="row">
                {{-- 1. Total penjualan per produk --}}
                <div class="col-md-6 mb-4">
                    <div class="card p-3 h-100">
                        <h6 class="mb-3">Total Penjualan per Produk</h6>
                        <div id="chartTotalProduk"></div>
                    </div>
                </div>

                {{-- 2. MAPE per produk --}}
                <div class="col-md-6 mb-4">
                    <div class="card p-3 h-100">
                        <h6 class="mb-3">MAPE per Produk (%)</h6>
                        <div id="chartMape"></div>
                    </div>
                </div>

                {{-- 3. Total penjualan per bulan (akumulasi) --}}
                <div class="col-md-6 mb-4">
                    <div class="card p-3 h-100">
                        <h6 class="mb-3">Total Penjualan per Bulan (Akumulasi)</h6>
                        <div id="chartTotalBulan"></div>
                    </div>
                </div>

                {{-- 4. Top 5 produk terlaris --}}
                <div class="col-md-6 mb-4">
                    <div class="card p-3 h-100">
                        <h6 class="mb-3">Top 5 Produk Terlaris</h6>
                        <div id="chartTopProduk"></div>
                    </div>
                </div>

                {{-- 5. Perbandingan aktual vs forecast (produk pertama) --}}
                <div class="col-md-12 mb-4">
                    <div class="card p-3">
                        <h6 class="mb-3">Aktual vs Forecast (12 Periode)</h6>
                        <div id="chartBandingAktual"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ApexCharts --}}
<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
<script>
    const CH = @json($charts);

    // 1) Total Penjualan per Produk
    new ApexCharts(document.querySelector("#chartTotalProduk"), {
        chart: { type: 'bar', height: 320 },
        series: [{ name: "Total Penjualan", data: CH.total_produk.values }],
        xaxis: { categories: CH.total_produk.labels, title: { text: 'Produk' } },
        yaxis: { title: { text: 'Jumlah Terjual' } },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: val => `${val}` } },
        legend: { position: 'top' }
    }).render();

    // 2) MAPE per Produk
    new ApexCharts(document.querySelector("#chartMape"), {
        chart: { type: 'bar', height: 320 },
        series: [{ name: "MAPE", data: CH.mape.values }],
        xaxis: { categories: CH.mape.labels, title: { text: 'Produk' } },
        yaxis: { title: { text: 'MAPE (%)' }, min: 0 },
        dataLabels: { enabled: false },
        tooltip: { y: { formatter: val => `${val.toFixed(2)}%` } },
        legend: { position: 'top' }
    }).render();

    // 3) Total Penjualan per Bulan (Akumulasi)
    new ApexCharts(document.querySelector("#chartTotalBulan"), {
        chart: { type: 'line', height: 320 },
        series: [{ name: "Penjualan", data: CH.total_bulan.values }],
        xaxis: { categories: CH.total_bulan.labels, title: { text: 'Bulan' } },
        yaxis: { title: { text: 'Jumlah Terjual' } },
        dataLabels: { enabled: false },
        stroke: { width: 3 },
        markers: { size: 4 },
        legend: { position: 'top' }
    }).render();

    // 4) Top 5 Produk Terlaris
    new ApexCharts(document.querySelector("#chartTopProduk"), {
        chart: { type: 'bar', height: 320 },
        series: [{ name: "Terjual", data: CH.top_produk.values }],
        xaxis: { categories: CH.top_produk.labels, title: { text: 'Produk' } },
        yaxis: { title: { text: 'Jumlah Terjual' } },
        dataLabels: { enabled: true },
        legend: { position: 'top' },
        plotOptions: { bar: { distributed: true } }
    }).render();

    // 5) Aktual vs Forecast
    new ApexCharts(document.querySelector("#chartBandingAktual"), {
        chart: { type: 'line', height: 360 },
        series: [
            { name: "Aktual", data: CH.banding_aktual.aktual },
            { name: "Forecast", data: CH.banding_aktual.forecast }
        ],
        xaxis: { categories: CH.banding_aktual.labels, title: { text: 'Periode' } },
        yaxis: { title: { text: 'Jumlah' } },
        dataLabels: { enabled: false },
        stroke: { width: 3 },
        markers: { size: 4 },
        legend: { position: 'top' },
        tooltip: {
            shared: true,
            y: { formatter: val => (val === null ? '-' : `${val}`) }
        }
    }).render();
</script>
@endsection
