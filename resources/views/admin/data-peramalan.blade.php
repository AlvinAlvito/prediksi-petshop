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
        <div class="activity">
            <div class="title">
                <i class="uil uil-clipboard-notes"></i>
                <span class="text">Data Peramalan Produk</span>
            </div>

            <div class="table-responsive">
                <table id="datatable" class="table table-hover table-striped align-middle">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Produk</th>
                            <th>Apr-2025</th>
                            <th>Mei-2025</th>
                            <th>Jun-2025</th>
                            <th>Jul-2025</th>
                            <th>Agu-2025</th>
                            <th>Sep-2025</th>
                            <th>Okt-2025</th>
                            <th style="width:110px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($peramalan as $idx => $row)
                            <tr onclick="window.location='{{ route('fts.semesta', ['id' => $row['penjualan_id']]) }}'"
                                style="cursor:pointer;">
                                <td>{{ $idx + 1 }}</td>
                                <td>{{ $row['produk'] }}</td>
                                <td>{{ $row['values']['April 2025'] ?? '-' }}</td>
                                <td>{{ $row['values']['Mei 2025'] ?? '-' }}</td>
                                <td>{{ $row['values']['Juni 2025'] ?? '-' }}</td>
                                <td>{{ $row['values']['Juli 2025'] ?? '-' }}</td>
                                <td>{{ $row['values']['Agustus 2025'] ?? '-' }}</td>
                                <td>{{ $row['values']['September 2025'] ?? '-' }}</td>
                                <td>{{ $row['values']['Oktober 2025'] ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('fts.semesta', ['id' => $row['penjualan_id']]) }}"
                                       class="btn btn-sm btn-primary">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Belum ada data peramalan.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</section>

{{-- DataTables --}}
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
    $(function () {
        $('#datatable').DataTable({
            pageLength: 25,
            ordering: false,
            autoWidth: false,
            scrollX: true
        });
    });
</script>
@endsection
