@extends('layouts.admin')

@section('title', 'Struk Transaksi')
@section('page-heading', 'Struk Transaksi')
@section('description', 'Rincian dan struk transaksi penjualan')

@push('styles')
    <style>
        .receipt { max-width: 420px; margin: 0 auto; background: #fff; font-family: 'Courier New', monospace; font-size: 13px; color: #212529; }
        .receipt .line { border-top: 1px dashed #6c757d; margin: .6rem 0; }
        .receipt .row-line { display: flex; justify-content: space-between; gap: .5rem; }
        .receipt .item-name { font-weight: 700; }
        @media print {
            body * { visibility: hidden; }
            .receipt, .receipt * { visibility: visible; }
            .receipt { position: absolute; left: 0; top: 0; width: 100%; max-width: none; box-shadow: none !important; border: 0 !important; }
        }
    </style>
@endpush

@section('content')
    @php
        $statusColors = ['paid' => 'success', 'partial' => 'warning', 'unpaid' => 'secondary'];
        $money = fn ($amount) => 'Rp'.number_format((float) $amount, 0, ',', '.');
    @endphp

    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h2 class="h3 mb-1 text-gray-800">Transaksi {{ $transaction->code }}</h2>
            <p class="mb-0 text-gray-600">
                {{ $transaction->transaction_date->format('d M Y') }}
                <span class="badge badge-{{ $statusColors[$transaction->payment_status] ?? 'secondary' }} ml-1">{{ $transaction->payment_status_label }}</span>
            </p>
        </div>
        <div class="mt-3 mt-sm-0 text-nowrap">
            <a href="{{ route('transactions.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Transaksi Baru
            </a>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                <i class="fas fa-print mr-1"></i> Cetak Struk
            </button>
            <a href="{{ route('transactions.edit', $transaction) }}" class="btn btn-warning">
                <i class="fas fa-edit mr-1"></i> Edit
            </a>
            <form action="{{ route('transactions.destroy', $transaction) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Hapus transaksi {{ $transaction->code }}? Stok produk akan dikembalikan.')">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash mr-1"></i> Hapus
                </button>
            </form>
            <a href="{{ route('transactions.index') }}" class="btn btn-light border">
                <i class="fas fa-list mr-1"></i> Daftar
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-5 mb-4">
            <div class="receipt card shadow p-4">
                <div class="text-center">
                    <div class="h5 font-weight-bold mb-0">TOKO KITA</div>
                    <div>Struk Penjualan</div>
                </div>
                <div class="line"></div>
                <div class="row-line"><span>No</span><span>{{ $transaction->code }}</span></div>
                <div class="row-line"><span>Tanggal</span><span>{{ $transaction->transaction_date->format('d/m/Y') }} {{ $transaction->created_at?->format('H:i') }}</span></div>
                <div class="row-line"><span>Pelanggan</span><span>{{ $transaction->customer?->name ?? 'Umum' }}</span></div>
                <div class="line"></div>
                @foreach ($transaction->products as $product)
                    <div class="item-name">{{ $product->name }}</div>
                    <div class="row-line">
                        <span>{{ $product->pivot->quantity }} x {{ number_format((float) $product->pivot->unit_price, 0, ',', '.') }}</span>
                        <span>{{ number_format((float) $product->pivot->subtotal, 0, ',', '.') }}</span>
                    </div>
                @endforeach
                <div class="line"></div>
                <div class="row-line"><span>Subtotal</span><span>{{ $money($transaction->subtotal) }}</span></div>
                @if ((float) $transaction->discount_amount > 0)
                    <div class="row-line">
                        <span>Diskon{{ $transaction->discount_type === 'percent' ? ' ('.rtrim(rtrim(number_format((float) $transaction->discount_value, 2, ',', '.'), '0'), ',').'%)' : '' }}</span>
                        <span>- {{ $money($transaction->discount_amount) }}</span>
                    </div>
                @endif
                @if ((float) $transaction->tax_amount > 0)
                    <div class="row-line">
                        <span>Pajak ({{ rtrim(rtrim(number_format((float) $transaction->tax_percent, 2, ',', '.'), '0'), ',') }}%)</span>
                        <span>+ {{ $money($transaction->tax_amount) }}</span>
                    </div>
                @endif
                <div class="row-line font-weight-bold" style="font-size: 15px"><span>TOTAL</span><span>{{ $money($transaction->total) }}</span></div>
                <div class="line"></div>
                <div class="row-line"><span>Bayar ({{ $transaction->payment_method_label }})</span><span>{{ $money($transaction->paid_amount) }}</span></div>
                @if ((float) $transaction->change_amount > 0)
                    <div class="row-line"><span>Kembalian</span><span>{{ $money($transaction->change_amount) }}</span></div>
                @endif
                @if ($transaction->payment_status !== 'paid')
                    <div class="row-line font-weight-bold"><span>Sisa Tagihan</span><span>{{ $money($transaction->due_amount) }}</span></div>
                @endif
                <div class="row-line"><span>Status</span><span>{{ $transaction->payment_status_label }}</span></div>
                @if ($transaction->notes)
                    <div class="line"></div>
                    <div>Catatan: {{ $transaction->notes }}</div>
                @endif
                <div class="line"></div>
                <div class="text-center">Terima kasih atas kunjungan Anda</div>
            </div>
        </div>

        <div class="col-lg-7 mb-4">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Pelanggan</h6>
                </div>
                <div class="card-body">
                    @if ($transaction->customer)
                        <div class="font-weight-bold text-gray-800">{{ $transaction->customer->name }}</div>
                        <div class="text-gray-600">{{ $transaction->customer->email }} · {{ $transaction->customer->phone }}</div>
                        @if ($transaction->customer->address)
                            <div class="small text-gray-600 mt-1">{{ $transaction->customer->address }}</div>
                        @endif
                    @else
                        <div class="text-gray-600">Pelanggan Umum (tanpa data pelanggan).</div>
                    @endif
                </div>
            </div>

            <div class="card shadow">
                <div class="card-header py-3 d-flex align-items-center justify-content-between">
                    <h6 class="m-0 font-weight-bold text-primary">Barang yang Dibeli</h6>
                    <span class="badge badge-info">{{ $transaction->products->count() }} produk</span>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0" width="100%" cellspacing="0">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Produk</th>
                                    <th scope="col" class="text-right">Harga</th>
                                    <th scope="col" class="text-center">Jumlah</th>
                                    <th scope="col" class="text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($transaction->products as $product)
                                    <tr>
                                        <td>
                                            <div class="font-weight-bold text-gray-800">{{ $product->name }}</div>
                                            <div class="small text-gray-600">SKU: {{ $product->sku }}</div>
                                        </td>
                                        <td class="text-right">{{ $money($product->pivot->unit_price) }}</td>
                                        <td class="text-center">{{ $product->pivot->quantity }}</td>
                                        <td class="text-right">{{ $money($product->pivot->subtotal) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr><td colspan="3" class="text-right">Subtotal</td><td class="text-right">{{ $money($transaction->subtotal) }}</td></tr>
                                <tr><td colspan="3" class="text-right">Diskon</td><td class="text-right">- {{ $money($transaction->discount_amount) }}</td></tr>
                                <tr><td colspan="3" class="text-right">Pajak</td><td class="text-right">+ {{ $money($transaction->tax_amount) }}</td></tr>
                                <tr class="font-weight-bold"><td colspan="3" class="text-right">Total</td><td class="text-right text-primary">{{ $money($transaction->total) }}</td></tr>
                                <tr><td colspan="3" class="text-right">Dibayar ({{ $transaction->payment_method_label }})</td><td class="text-right">{{ $money($transaction->paid_amount) }}</td></tr>
                                <tr><td colspan="3" class="text-right">{{ $transaction->payment_status === 'paid' ? 'Kembalian' : 'Sisa Tagihan' }}</td><td class="text-right">{{ $money($transaction->payment_status === 'paid' ? $transaction->change_amount : $transaction->due_amount) }}</td></tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
