@extends('layouts.pos')

@section('title', 'Edit Transaksi')
@section('description', 'Perbarui transaksi penjualan')

@section('content')
    <form action="{{ route('transactions.update', $transaction) }}" method="POST" novalidate class="pos-form">
        @csrf
        @method('PUT')
        @include('transactions.partials.form', ['submitLabel' => 'Simpan Perubahan', 'heading' => 'Edit '.$transaction->code])
    </form>
@endsection
