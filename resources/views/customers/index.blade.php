@extends('layouts.admin')

@section('title', 'Daftar Pelanggan')
@section('page-heading', 'Pelanggan')
@section('description', 'Kelola data pelanggan yang melakukan transaksi')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="h3 mb-1 text-gray-800">Daftar Pelanggan</h2>
            <p class="mb-0 text-gray-600">Data pelanggan digunakan sebagai pembeli pada setiap transaksi.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="btn btn-primary shadow-sm mt-3 mt-sm-0">
            <i class="fas fa-user-plus fa-sm text-white-50 mr-1"></i> Tambah Pelanggan
        </a>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">Data Pelanggan</h6>
            <span class="badge badge-primary">{{ $customers->total() }} pelanggan</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Pelanggan</th>
                            <th scope="col">Telepon</th>
                            <th scope="col" class="text-center">Transaksi</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $index => $customer)
                            <tr>
                                <td>{{ $customers->firstItem() + $index }}</td>
                                <td>
                                    <div class="font-weight-bold text-gray-800">{{ $customer->name }}</div>
                                    <div class="small text-gray-600">{{ $customer->email }}</div>
                                </td>
                                <td>{{ $customer->phone }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $customer->transactions_count > 0 ? 'info' : 'secondary' }}">
                                        {{ $customer->transactions_count }} transaksi
                                    </span>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('customers.edit', $customer) }}" class="btn btn-warning btn-sm"
                                        aria-label="Edit {{ $customer->name }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus pelanggan {{ $customer->name }}?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            aria-label="Hapus {{ $customer->name }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <i class="fas fa-address-book fa-2x text-gray-300 mb-3"></i>
                                    <div class="font-weight-bold text-gray-700">Belum ada pelanggan</div>
                                    <p class="text-gray-600 mb-3">Tambahkan pelanggan pertama sebelum membuat transaksi.</p>
                                    <a href="{{ route('customers.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-user-plus mr-1"></i> Tambah Pelanggan
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $customers->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
