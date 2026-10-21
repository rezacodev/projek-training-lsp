@extends('layouts.admin')

@section('title', 'Edit Pelanggan')
@section('page-heading', 'Edit Pelanggan')
@section('description', 'Perbarui data pelanggan')

@section('content')
    <div class="mb-4">
        <h2 class="h3 mb-1 text-gray-800">Edit Pelanggan</h2>
        <p class="mb-0 text-gray-600">Perbarui data kontak pelanggan.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center">
            <i class="fas fa-edit text-primary mr-2"></i>
            <h6 class="m-0 font-weight-bold text-primary">Perbarui Informasi Pelanggan</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('customers.update', $customer) }}" method="POST">
                @csrf
                @method('PUT')
                @include('customers.partials.form', ['submitLabel' => 'Simpan Perubahan', 'customer' => $customer])
            </form>
        </div>
    </div>
@endsection
