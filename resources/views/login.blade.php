<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="description" content="">
    <meta name="author" content="">

    <title>Prediksi Sembako</title>

    <!-- CSS FILES -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@500;600;700&family=Open+Sans&display=swap"
        rel="stylesheet">

    <link href="css/bootstrap.min.css" rel="stylesheet">

    <link href="css/bootstrap-icons.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <link href="css/templatemo-topic-listing.css" rel="stylesheet">

</head>

<body id="top">

    <main>

        <nav class="navbar navbar-expand-lg">
            <div class="container">
                <a class="navbar-brand" href="index.html">
                    <i class="bi-back"></i>
                    <span>Prediksi Sembako</span>
                </a>

                <div class="d-lg-none ms-auto me-4">
                    <a href="#top" class="navbar-icon bi-person smoothscroll"></a>
                </div>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                    aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <!-- Modal -->
                <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog">
                        <form method="POST" action="/" class="modal-content">
                            @csrf
                            <div class="modal-header">
                                <h5 class="modal-title">Login Admin</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                @if ($errors->has('login'))
                                    <div class="alert alert-danger">{{ $errors->first('login') }}</div>
                                @endif
                                <div class="mb-3">
                                    <label>Username: admin</label>
                                    <input type="text" name="username" class="form-control" required>
                                </div>
                                <div class="mb-3">
                                    <label>Password: 123</label>
                                    <input type="password" name="password" class="form-control" required>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button class="btn btn-primary">Login</button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav ms-lg-5 me-lg-auto">
                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_1">Beranda</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_2">Tentang Kami</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_3">Fitur</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_4">Hasil Perhitungan</a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link click-scroll" href="#section_5">Kontak</a>
                        </li>
                    </ul>

                    <div class="d-none d-lg-block">
                        <a href="#top" data-bs-toggle="modal" data-bs-target="#loginModal"
                            class="navbar-icon bi-person smoothscroll"></a>
                    </div>
                </div>
            </div>
        </nav>


        <section class="hero-section d-flex justify-content-center align-items-center" id="section_1">
            <div class="container">
                <div class="row">

                    <div class="col-lg-10 col-12 mx-auto text-center">
                        <h1 class="text-white">Sistem Prediksi Penjualan Sembako</h1>

                        <h6 class="text-white mt-3">
                            Visualisasi & Prediksi Penjualan Menggunakan Metode Regresi Linier Sederhana
                        </h6>

                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal"
                            class="btn btn-light btn-lg mt-4">
                            Masuk Dashboard
                        </a>
                    </div>

                </div>
            </div>
        </section>

        <section class="featured-section">
            <div class="container">
                <div class="row justify-content-center">

                    <div class="col-lg-4 col-12 mb-4 mb-lg-0">
                        <div class="custom-block bg-white shadow-lg">
                            <div class="d-flex">
                                <div>
                                    <h5 class="mb-2">Manajemen Data Penjualan</h5>
                                    <p class="mb-0">Admin dapat menambahkan data penjualan per bulan untuk setiap
                                        produk sembako, seperti jumlah dan harga satuan.</p>
                                </div>
                                <span class="badge bg-success rounded-pill ms-auto">✓</span>
                            </div>

                            <img src="images/topics/undraw_Educator_re_ju47.png" class="custom-block-image img-fluid"
                                alt="Input Data Penjualan">
                        </div>
                    </div>

                    <div class="col-lg-4 col-12 mb-4 mb-lg-0">
                        <div class="custom-block bg-white shadow-lg">
                            <div class="d-flex">
                                <div>
                                    <h5 class="mb-2">Prediksi Otomatis</h5>
                                    <p class="mb-0">Sistem menghitung prediksi penjualan 6 bulan ke depan secara
                                        otomatis dengan metode regresi linier sederhana.</p>
                                </div>
                                <span class="badge bg-primary rounded-pill ms-auto">✓</span>
                            </div>

                            <img src="images/topics/colleagues-working-cozy-office-medium-shot.png"
                                class="custom-block-image img-fluid" alt="Prediksi">
                        </div>
                    </div>

                    <div class="col-lg-4 col-12">
                        <div class="custom-block bg-white shadow-lg">
                            <div class="d-flex">
                                <div>
                                    <h5 class="mb-2">Visualisasi dan Analisis</h5>
                                    <p class="mb-0">Tampilkan grafik perbandingan data aktual dan hasil prediksi per
                                        produk, serta nilai akurasi (MAPE).</p>
                                </div>
                                <span class="badge bg-info rounded-pill ms-auto">✓</span>
                            </div>

                            <img src="images/topics/undraw_Finance_re_gnv2.png" class="custom-block-image img-fluid"
                                alt="Visualisasi Data">
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <section class="timeline-section section-padding mt-5" id="section_3">
            <div class="section-overlay"></div>

            <div class="container">
                <div class="row">

                    <div class="col-12 text-center">
                        <h2 class="text-white mb-4">Bagaimana Sistem Ini Bekerja?</h2>
                    </div>

                    <div class="col-lg-10 col-12 mx-auto">
                        <div class="timeline-container">
                            <ul class="vertical-scrollable-timeline" id="vertical-scrollable-timeline">
                                <div class="list-progress">
                                    <div class="inner"></div>
                                </div>

                                <li>
                                    <h4 class="text-white mb-3">1. Input Data Penjualan Produk</h4>
                                    <p class="text-white">
                                        Admin menginput data penjualan untuk masing-masing produk sembako dari bulan
                                        Januari hingga Juni.
                                        Data ini meliputi nama produk, harga satuan, dan jumlah penjualan per bulan.
                                    </p>
                                    <div class="icon-holder">
                                        <i class="bi-pencil-square"></i>
                                    </div>
                                </li>

                                <li>
                                    <h4 class="text-white mb-3">2. Sistem Proses Regresi Linier</h4>
                                    <p class="text-white">
                                        Setelah data disimpan, sistem secara otomatis menghitung persamaan regresi
                                        linier sederhana
                                        untuk setiap produk. Nilai koefisien (b), intercept (a), dan MAPE dihitung
                                        sebagai dasar prediksi.
                                    </p>
                                    <div class="icon-holder">
                                        <i class="bi-gear-fill"></i>
                                    </div>
                                </li>

                                <li>
                                    <h4 class="text-white mb-3">3. Prediksi Penjualan 6 Bulan ke Depan</h4>
                                    <p class="text-white">
                                        Berdasarkan persamaan regresi, sistem menghasilkan prediksi penjualan dari bulan
                                        Juli hingga Desember.
                                        Hasil ini disimpan dan dapat dilihat dalam bentuk tabel dan grafik.
                                    </p>
                                    <div class="icon-holder">
                                        <i class="bi-bar-chart-line-fill"></i>
                                    </div>
                                </li>

                                <li>
                                    <h4 class="text-white mb-3">4. Lihat Visualisasi & Detail Produk</h4>
                                    <p class="text-white">
                                        Pengguna dapat melihat detail lengkap untuk setiap produk, termasuk data aktual,
                                        hasil prediksi,
                                        nilai MAPE, dan grafik perbandingan. Informasi ini berguna untuk pengambilan
                                        keputusan bisnis.
                                    </p>
                                    <div class="icon-holder">
                                        <i class="bi-file-earmark-bar-graph"></i>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div class="col-12 text-center mt-5">
                        <p class="text-white">
                            Ingin mengetahui prediksi produk Anda?
                            <a href="#" class="btn custom-btn custom-border-btn ms-3">Hubungi Kami</a>
                        </p>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <footer class="site-footer section-padding">
        <div class="container">
            <div class="row">

                {{-- Brand dan Deskripsi --}}
                <div class="col-lg-3 col-12 mb-4 pb-2">
                    <a class="navbar-brand mb-2" href="/">
                        <i class="bi bi-bar-chart-line-fill"></i>
                        <span>Sembako</span>
                    </a>
                    <p class="text-white">
                        Sistem prediksi penjualan sembako menggunakan metode regresi linier sederhana. Membantu
                        pengambilan keputusan bisnis dengan data yang akurat dan visual.
                    </p>
                </div>

                {{-- Navigasi Menu --}}
                <div class="col-lg-3 col-md-4 col-6">
                    <h6 class="site-footer-title mb-3">Menu</h6>
                    <ul class="site-footer-links">
                        <li class="site-footer-link-item"><a href="/" class="site-footer-link">Beranda</a></li>
                        <li class="site-footer-link-item"><a href="/admin/data-penjualan"
                                class="site-footer-link">Data Penjualan</a></li>
                        <li class="site-footer-link-item"><a href="/admin/koefisien"
                                class="site-footer-link">Koefisien</a></li>
                        <li class="site-footer-link-item"><a href="/admin/hasil-prediksi"
                                class="site-footer-link">Hasil Prediksi</a></li>
                    </ul>
                </div>

                {{-- Kontak --}}
                <div class="col-lg-3 col-md-4 col-6 mb-4 mb-lg-0">
                    <h6 class="site-footer-title mb-3">Kontak</h6>
                    <p class="text-white d-flex mb-1">
                        <a href="tel:081234567890" class="site-footer-link">0812-3456-7890</a>
                    </p>
                    <p class="text-white d-flex">
                        <a href="mailto:admin@prediksisembako.id"
                            class="site-footer-link">admin@prediksisembako.id</a>
                    </p>
                </div>

                {{-- Bahasa dan Hak Cipta --}}
                <div class="col-lg-3 col-md-4 col-12 mt-4 mt-lg-0 ms-auto">
                    <div class="dropdown">
                        <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            Bahasa Indonesia
                        </button>
                        <ul class="dropdown-menu">
                            <li><button class="dropdown-item" type="button">English</button></li>
                            <li><button class="dropdown-item" type="button">Melayu</button></li>
                        </ul>
                    </div>

                    <p class="copyright-text mt-lg-5 mt-4">
                        © 2025 Prediksi Penjualan Sembako.<br>
                        All rights reserved.<br><br>
                    </p>
                </div>

            </div>
        </div>
    </footer>



    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous">
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.min.js"
        integrity="sha384-7qAoOXltbVP82dhxHAUje59V5r2YsVfBafyUDxEdApLPmcdhBPg1DKg1ERo0BZlK" crossorigin="anonymous">
    </script>
    <!-- JAVASCRIPT FILES -->
    <script src="script/jquery.min.js"></script>
    <script src="script/bootstrap.bundle.min.js"></script>
    <script src="script/jquery.sticky.js"></script>
    <script src="script/click-scroll.js"></script>
    <script src="script/custom.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

</body>

</html>
