@extends('layouts.admin')

@section('title', 'Tambah Produk')
@section('page-heading', 'Tambah Produk')
@section('description', 'Tambahkan katalog produk baru ke inventaris')

@section('content')
    <div class="mb-4">
        <h2 class="h3 mb-1 text-gray-800">Tambah Produk</h2>
        <p class="mb-0 text-gray-600">Catat identitas, kategori, harga, dan jumlah stok produk.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-box-open text-primary mr-2"></i>
            <h6 class="m-0 font-weight-bold text-primary">Informasi Produk</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @include('products.partials.form', ['submitLabel' => 'Simpan Produk'])
            </form>
        </div>
    </div>
@endsection
