@extends('layouts.pos')

@section('title', 'Kasir')
@section('description', 'Kasir penjualan layar penuh')

@section('content')
    <form action="{{ route('transactions.store') }}" method="POST" novalidate class="pos-form">
        @csrf
        @include('transactions.partials.form', ['submitLabel' => 'Bayar & Simpan', 'heading' => 'Kasir'])
    </form>
@endsection
