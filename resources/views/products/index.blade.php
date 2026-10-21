@extends('layouts.admin')

@section('title', 'Inventaris Produk')
@section('page-heading', 'Produk')
@section('description', 'Kelola katalog produk, kategori, harga, dan ketersediaan stok')

@section('content')
    <div class="card bg-gradient-primary text-white shadow mb-4">
        <div class="card-body d-flex flex-column flex-md-row align-items-md-center justify-content-between">
            <div class="mb-3 mb-md-0">
                <div class="h4 font-weight-bold mb-2">Inventaris Produk</div>
                <p class="mb-0">
                    Gunakan menu ini untuk mencatat katalog, mengelompokkan kategori, memantau harga,
                    dan memastikan stok produk tetap terkontrol.
                </p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-light text-primary shadow-sm text-nowrap">
                <i class="fas fa-plus mr-1"></i> Tambah Produk
            </a>
        </div>
    </div>

    <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
        <div>
            <h2 class="h4 mb-1 text-gray-800">Daftar Produk</h2>
            <p class="mb-0 text-gray-600">Semua data inventaris yang sudah dicatat.</p>
        </div>
        <span class="badge badge-success px-3 py-2 mt-2 mt-sm-0">
            {{ $products->total() }} produk terdaftar
        </span>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Katalog Inventaris</h6>
            <i class="fas fa-boxes text-gray-400"></i>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Produk</th>
                            <th scope="col">Kategori</th>
                            <th scope="col" class="text-right">Harga</th>
                            <th scope="col" class="text-center">Stok</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $index => $product)
                            <tr>
                                <td>{{ $products->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if ($product->image_url)
                                            <img src="{{ $product->image_url }}" alt="" class="rounded border mr-2"
                                                style="width: 48px; height: 36px; object-fit: cover;">
                                        @else
                                            <span class="rounded border mr-2 d-inline-flex align-items-center justify-content-center bg-light text-gray-400"
                                                style="width: 48px; height: 36px;"><i class="fas fa-box"></i></span>
                                        @endif
                                        <div>
                                            <div class="font-weight-bold text-gray-800">{{ $product->name }}</div>
                                            <div class="small text-gray-600">SKU: {{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td><span class="badge badge-light border">{{ $product->category }}</span></td>
                                <td class="text-right">Rp{{ number_format((float) $product->price, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $product->stock > 0 ? 'success' : 'secondary' }}">
                                        {{ $product->stock }} unit
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm"
                                        aria-label="Edit {{ $product->name }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Hapus produk {{ $product->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            aria-label="Hapus {{ $product->name }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <i class="fas fa-box-open fa-2x text-gray-300 mb-3"></i>
                                    <div class="font-weight-bold text-gray-700">Inventaris masih kosong</div>
                                    <p class="text-gray-600 mb-3">
                                        Tambahkan produk pertama agar katalog dan stok mulai tercatat.
                                    </p>
                                    <a href="{{ route('products.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus mr-1"></i> Tambah Produk
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $products->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
