@extends('layouts.admin')

@section('title', 'Daftar Transaksi')
@section('page-heading', 'Transaksi')
@section('description', 'Riwayat transaksi penjualan')

@section('content')
    @php
        $statusColors = ['paid' => 'success', 'partial' => 'warning', 'unpaid' => 'secondary'];
    @endphp

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="h3 mb-1 text-gray-800">Transaksi Penjualan</h2>
            <p class="mb-0 text-gray-600">Riwayat penjualan beserta status pembayaran. Satu transaksi dapat berisi banyak produk.</p>
        </div>
        <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-lg shadow-sm mt-3 mt-sm-0">
            <i class="fas fa-cash-register mr-1"></i> Transaksi Baru (Kasir)
        </a>
    </div>

    <div class="row">
        <div class="col-md-4 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Penjualan Hari Ini</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp{{ number_format($todaySales, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Transaksi Hari Ini</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $todayCount }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Total Piutang</div>
                    <div class="h5 mb-0 font-weight-bold text-gray-800">Rp{{ number_format($receivable, 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3">
            <form action="{{ route('transactions.index') }}" method="GET" class="form-inline">
                <label for="q" class="sr-only">Cari</label>
                <input type="search" id="q" name="q" value="{{ $search }}" class="form-control mr-2 mb-2 mb-sm-0"
                    placeholder="Cari kode atau pelanggan...">
                <label for="status" class="sr-only">Status pembayaran</label>
                <select id="status" name="status" class="custom-select mr-2 mb-2 mb-sm-0">
                    <option value="">Semua status</option>
                    @foreach (\App\Models\Transaction::PAYMENT_STATUSES as $key => $label)
                        <option value="{{ $key }}" @selected($status === $key)>{{ $label }}</option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-primary mr-2"><i class="fas fa-search mr-1"></i> Cari</button>
                @if ($search !== '' || $status)
                    <a href="{{ route('transactions.index') }}" class="btn btn-light border">Reset</a>
                @endif
                <span class="badge badge-success ml-auto px-3 py-2">{{ $transactions->total() }} transaksi</span>
            </form>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered" width="100%" cellspacing="0">
                    <thead class="thead-light">
                        <tr>
                            <th scope="col">Kode</th>
                            <th scope="col">Tanggal</th>
                            <th scope="col">Pelanggan</th>
                            <th scope="col" class="text-center">Barang</th>
                            <th scope="col" class="text-right">Total</th>
                            <th scope="col" class="text-center">Pembayaran</th>
                            <th scope="col" class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $transaction)
                            <tr>
                                <td class="font-weight-bold text-gray-800">{{ $transaction->code }}</td>
                                <td>{{ $transaction->transaction_date->format('d M Y') }}</td>
                                <td>{{ $transaction->customer?->name ?? 'Pelanggan Umum' }}</td>
                                <td class="text-center">
                                    <span class="badge badge-info">{{ $transaction->products_count }} produk</span>
                                    <div class="small text-gray-600">{{ (int) $transaction->total_quantity }} unit</div>
                                </td>
                                <td class="text-right">Rp{{ number_format((float) $transaction->total, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $statusColors[$transaction->payment_status] ?? 'secondary' }}">
                                        {{ $transaction->payment_status_label }}
                                    </span>
                                    <div class="small text-gray-600">{{ $transaction->payment_method_label }}</div>
                                    @if ($transaction->payment_status !== 'paid')
                                        <div class="small text-danger">Sisa Rp{{ number_format($transaction->due_amount, 0, ',', '.') }}</div>
                                    @endif
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="{{ route('transactions.show', $transaction) }}"
                                        class="btn btn-info btn-sm" aria-label="Detail {{ $transaction->code }}">
                                        <i class="fas fa-receipt"></i> Struk
                                    </a>
                                    <a href="{{ route('transactions.edit', $transaction) }}"
                                        class="btn btn-warning btn-sm" aria-label="Edit {{ $transaction->code }}">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <form action="{{ route('transactions.destroy', $transaction) }}" method="POST"
                                        class="d-inline"
                                        onsubmit="return confirm('Hapus transaksi {{ $transaction->code }}? Stok produk akan dikembalikan.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            aria-label="Hapus {{ $transaction->code }}">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5">
                                    <i class="fas fa-cash-register fa-2x text-gray-300 mb-3"></i>
                                    @if ($search !== '' || $status)
                                        <div class="font-weight-bold text-gray-700">Tidak ada transaksi yang cocok</div>
                                        <p class="text-gray-600 mb-3">Coba ubah kata kunci atau status pembayaran.</p>
                                    @else
                                        <div class="font-weight-bold text-gray-700">Belum ada transaksi</div>
                                        <p class="text-gray-600 mb-3">Buka kasir untuk mencatat penjualan pertama.</p>
                                        <a href="{{ route('transactions.create') }}" class="btn btn-primary btn-sm">
                                            <i class="fas fa-cash-register mr-1"></i> Transaksi Baru (Kasir)
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $transactions->links('pagination::bootstrap-4') }}
        </div>
    </div>
@endsection
