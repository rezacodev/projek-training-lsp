@extends('layouts.admin')

@section('title', 'Tambah Pelanggan')
@section('page-heading', 'Tambah Pelanggan')
@section('description', 'Tambahkan pelanggan baru')

@section('content')
    <div class="mb-4">
        <h2 class="h3 mb-1 text-gray-800">Tambah Pelanggan</h2>
        <p class="mb-0 text-gray-600">Isi data kontak pelanggan yang akan melakukan pembelian.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-address-book text-primary mr-2"></i>
            <h6 class="m-0 font-weight-bold text-primary">Informasi Pelanggan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.store') }}" method="POST">
                @csrf
                @include('customers.partials.form', ['submitLabel' => 'Simpan Pelanggan'])
            </form>
        </div>
    </div>
@endsection
