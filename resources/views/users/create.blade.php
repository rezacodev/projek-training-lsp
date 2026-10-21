@extends('layouts.admin')

@section('title', 'Tambah Pengguna')
@section('page-heading', 'Tambah Pengguna')

@section('content')
    <div class="mb-4">
        <h2 class="h3 mb-1 text-gray-800">Tambah Pengguna</h2>
        <p class="mb-0 text-gray-600">Isi informasi pengguna baru di bawah ini.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Pengguna</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                @include('users.partials.form', ['submitLabel' => 'Simpan Pengguna'])
            </form>
        </div>
    </div>
@endsection
