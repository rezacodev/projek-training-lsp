@extends('layouts.admin')

@section('title', 'Beranda')
@section('page-heading', 'Beranda')
@section('description', 'Beranda dashboard administrasi')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="h3 mb-1 text-gray-800">Selamat Datang</h2>
            <p class="mb-0 text-gray-600">Pilih menu di sidebar untuk mengelola aplikasi.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary shadow-sm mt-3 mt-sm-0">
            <i class="fas fa-shopping-cart fa-sm text-white-50 mr-1"></i> Buat Transaksi
        </a>
    </div>

    <div class="row">
        @php
            $stats = [
                ['label' => 'Total Pengguna', 'value' => $usersCount, 'icon' => 'users', 'color' => 'primary'],
                ['label' => 'Total Produk', 'value' => $productsCount, 'icon' => 'box-open', 'color' => 'success'],
                ['label' => 'Total Pelanggan', 'value' => $customersCount, 'icon' => 'address-book', 'color' => 'info'],
                ['label' => 'Total Transaksi', 'value' => $transactionsCount, 'icon' => 'shopping-cart', 'color' => 'warning'],
            ];
        @endphp
        @foreach ($stats as $stat)
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-{{ $stat['color'] }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $stat['color'] }} text-uppercase mb-1">
                                    {{ $stat['label'] }}</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $stat['value'] }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-{{ $stat['icon'] }} fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="card bg-gradient-primary text-white shadow mb-4">
        <div class="card-body d-flex align-items-center justify-content-between">
            <div>
                <div class="text-xs font-weight-bold text-uppercase mb-1">Total Penjualan</div>
                <div class="h3 mb-0 font-weight-bold">Rp{{ number_format($revenue, 0, ',', '.') }}</div>
            </div>
            <i class="fas fa-wallet fa-2x text-white-50"></i>
        </div>
    </div>

    <div class="row">
        @php
            $shortcuts = [
                ['title' => 'Daftar Pengguna', 'text' => 'Lihat, edit, atau hapus data pengguna.', 'route' => 'users.index', 'icon' => 'arrow-right', 'color' => 'primary'],
                ['title' => 'Tambah Pengguna', 'text' => 'Buat data pengguna baru.', 'route' => 'users.create', 'icon' => 'user-plus', 'color' => 'success'],
                ['title' => 'Inventaris Produk', 'text' => 'Kelola katalog, kategori, harga, dan stok.', 'route' => 'products.index', 'icon' => 'arrow-right', 'color' => 'success'],
                ['title' => 'Tambah Produk', 'text' => 'Catat produk baru ke dalam inventaris.', 'route' => 'products.create', 'icon' => 'plus-circle', 'color' => 'primary'],
                ['title' => 'Daftar Pelanggan', 'text' => 'Kelola data pelanggan pembeli.', 'route' => 'customers.index', 'icon' => 'arrow-right', 'color' => 'info'],
                ['title' => 'Tambah Pelanggan', 'text' => 'Daftarkan pelanggan baru.', 'route' => 'customers.create', 'icon' => 'user-plus', 'color' => 'info'],
                ['title' => 'Daftar Transaksi', 'text' => 'Riwayat penjualan dan status pembayaran.', 'route' => 'transactions.index', 'icon' => 'arrow-right', 'color' => 'warning'],
                ['title' => 'Kasir (Transaksi Baru)', 'text' => 'Catat penjualan dengan banyak produk.', 'route' => 'transactions.create', 'icon' => 'cash-register', 'color' => 'warning'],
            ];
        @endphp
        @foreach ($shortcuts as $shortcut)
            <div class="col-xl-3 col-md-6 mb-4">
                <a href="{{ route($shortcut['route']) }}" class="text-decoration-none">
                    <div class="card shadow h-100 py-2">
                        <div class="card-body d-flex align-items-center justify-content-between">
                            <div>
                                <div class="font-weight-bold text-gray-800">{{ $shortcut['title'] }}</div>
                                <div class="small text-gray-600">{{ $shortcut['text'] }}</div>
                            </div>
                            <i class="fas fa-{{ $shortcut['icon'] }} text-{{ $shortcut['color'] }}"></i>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>
@endsection
