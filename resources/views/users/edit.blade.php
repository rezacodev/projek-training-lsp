@extends('layouts.admin')

@section('title', 'Edit Pengguna')
@section('page-heading', 'Edit Pengguna')

@section('content')
    <div class="mb-4">
        <h2 class="h3 mb-1 text-gray-800">Edit Pengguna</h2>
        <p class="mb-0 text-gray-600">Perbarui nama, email, atau kata sandi pengguna.</p>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <h6 class="m-0 font-weight-bold text-primary">Informasi Pengguna</h6>
        </div>
        <div class="card-body">
            <form action="{{ route('users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                @include('users.partials.form', ['submitLabel' => 'Simpan Perubahan', 'user' => $user])
            </form>
        </div>
    </div>
@endsection
