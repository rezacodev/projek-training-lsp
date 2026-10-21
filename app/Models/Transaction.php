<?php

namespace App\Models;

use Database\Factories\TransactionFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Transaction extends Model
{
    /** @use HasFactory<TransactionFactory> */
    use HasFactory;

    public const PAYMENT_METHODS = [
        'cash' => 'Tunai',
        'transfer' => 'Transfer Bank',
        'qris' => 'QRIS',
        'debit' => 'Kartu Debit',
    ];

    public const PAYMENT_STATUSES = [
        'paid' => 'Lunas',
        'partial' => 'Sebagian',
        'unpaid' => 'Belum Dibayar',
    ];

    protected $fillable = [
        'code',
        'customer_id',
        'transaction_date',
        'subtotal',
        'discount_type',
        'discount_value',
        'discount_amount',
        'tax_percent',
        'tax_amount',
        'total',
        'payment_method',
        'paid_amount',
        'change_amount',
        'payment_status',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'transaction_date' => 'date',
            'subtotal' => 'decimal:2',
            'discount_value' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'tax_percent' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'total' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'change_amount' => 'decimal:2',
        ];
    }

    protected function paymentMethodLabel(): Attribute
    {
        return Attribute::get(fn () => self::PAYMENT_METHODS[$this->payment_method] ?? $this->payment_method);
    }

    protected function paymentStatusLabel(): Attribute
    {
        return Attribute::get(fn () => self::PAYMENT_STATUSES[$this->payment_status] ?? $this->payment_status);
    }

    protected function dueAmount(): Attribute
    {
        return Attribute::get(fn () => max(round((float) $this->total - (float) $this->paid_amount, 2), 0.0));
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)
            ->withPivot(['quantity', 'unit_price', 'subtotal'])
            ->withTimestamps();
    }
}
