<?php

namespace App\Http\Requests;

use App\Models\Transaction;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $discountMax = $this->input('discount_type') === 'percent' ? 'max:100' : 'max:9999999999.99';

        return [
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string', 'max:500'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:1000000'],
            'discount_type' => ['nullable', Rule::in(['amount', 'percent'])],
            'discount_value' => ['nullable', 'numeric', 'min:0', $discountMax],
            'tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'payment_method' => ['required', Rule::in(array_keys(Transaction::PAYMENT_METHODS))],
            'paid_amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_id.exists' => 'Pelanggan yang dipilih tidak ditemukan.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'transaction_date.date' => 'Format tanggal transaksi tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal transaksi tidak boleh melewati hari ini.',
            'notes.max' => 'Catatan maksimal :max karakter.',
            'items.required' => 'Tambahkan minimal satu produk pada transaksi.',
            'items.min' => 'Tambahkan minimal satu produk pada transaksi.',
            'items.*.product_id.required' => 'Pilih produk pada baris :position.',
            'items.*.product_id.exists' => 'Produk pada baris :position tidak ditemukan.',
            'items.*.product_id.distinct' => 'Produk pada baris :position dipilih lebih dari sekali.',
            'items.*.quantity.required' => 'Jumlah pada baris :position wajib diisi.',
            'items.*.quantity.integer' => 'Jumlah pada baris :position harus berupa bilangan bulat.',
            'items.*.quantity.min' => 'Jumlah pada baris :position minimal :min.',
            'items.*.quantity.max' => 'Jumlah pada baris :position maksimal :max.',
            'discount_type.in' => 'Jenis diskon tidak valid.',
            'discount_value.numeric' => 'Diskon harus berupa angka.',
            'discount_value.min' => 'Diskon tidak boleh negatif.',
            'discount_value.max' => 'Nilai diskon melebihi batas yang diizinkan.',
            'tax_percent.numeric' => 'Pajak harus berupa angka.',
            'tax_percent.min' => 'Pajak tidak boleh negatif.',
            'tax_percent.max' => 'Pajak maksimal :max%.',
            'payment_method.required' => 'Pilih metode pembayaran.',
            'payment_method.in' => 'Metode pembayaran tidak valid.',
            'paid_amount.numeric' => 'Jumlah bayar harus berupa angka.',
            'paid_amount.min' => 'Jumlah bayar tidak boleh negatif.',
            'paid_amount.max' => 'Jumlah bayar melebihi batas yang diizinkan.',
        ];
    }
}
