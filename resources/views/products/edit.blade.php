@extends('layouts.admin')

@section('title', 'Edit Produk')
@section('page-heading', 'Edit Produk')
@section('description', 'Perbarui informasi dan ketersediaan stok produk')

@section('content')
    <div class="mb-4">
        <h2 class="h3 mb-1 text-gray-800">Edit Produk</h2>
        <p class="mb-0 text-gray-600">Perbarui data produk. Perubahan SKU harus tetap unik.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-edit text-primary mr-2"></i>
            <h6 class="m-0 font-weight-bold text-primary">Perbarui Informasi Produk</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('products.partials.form', ['submitLabel' => 'Simpan Perubahan', 'product' => $product])
            </form>
        </div>
    </div>
@endsection
