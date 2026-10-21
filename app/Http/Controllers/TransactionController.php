<?php

namespace App\Http\Controllers;

use App\Http\Requests\TransactionRequest;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Services\TransactionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function __construct(private readonly TransactionService $transactions) {}

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));
        $status = $request->query('status');

        $transactions = Transaction::query()
            ->with('customer')
            ->withCount('products')
            ->withSum('products as total_quantity', 'product_transaction.quantity')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.str_replace(['%', '_'], ['\%', '\_'], $search).'%';

                $query->where(function ($query) use ($like) {
                    $query->where('code', 'like', $like)
                        ->orWhereHas('customer', fn ($customer) => $customer->where('name', 'like', $like));
                });
            })
            ->when(array_key_exists((string) $status, Transaction::PAYMENT_STATUSES), fn ($query) => $query->where('payment_status', $status))
            ->latest('transaction_date')
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('transactions.index', [
            'transactions' => $transactions,
            'search' => $search,
            'status' => $status,
            'todaySales' => (float) Transaction::query()->whereDate('transaction_date', today())->sum('total'),
            'todayCount' => Transaction::query()->whereDate('transaction_date', today())->count(),
            'receivable' => Transaction::query()
                ->where('payment_status', '!=', 'paid')
                ->get(['total', 'paid_amount'])
                ->sum(fn (Transaction $transaction) => $transaction->due_amount),
        ]);
    }

    public function create(): View
    {
        return view('transactions.create', $this->formData());
    }

    public function store(TransactionRequest $request): RedirectResponse
    {
        $transaction = $this->transactions->create($request->validated());

        return to_route('transactions.show', $transaction)
            ->with('success', "Transaksi {$transaction->code} berhasil dibuat dan stok produk diperbarui.".$this->paymentNote($transaction));
    }

    public function show(Transaction $transaction): View
    {
        $transaction->load(['customer', 'products']);

        return view('transactions.show', compact('transaction'));
    }

    public function edit(Transaction $transaction): View
    {
        $transaction->load('products');

        return view('transactions.edit', $this->formData($transaction) + ['transaction' => $transaction]);
    }

    public function update(TransactionRequest $request, Transaction $transaction): RedirectResponse
    {
        $this->transactions->update($transaction, $request->validated());

        return to_route('transactions.show', $transaction)
            ->with('success', "Transaksi {$transaction->code} berhasil diperbarui dan stok disesuaikan.".$this->paymentNote($transaction));
    }

    public function destroy(Transaction $transaction): RedirectResponse
    {
        $code = $transaction->code;

        $this->transactions->delete($transaction);

        return to_route('transactions.index')
            ->with('success', "Transaksi {$code} berhasil dihapus dan stok produk dikembalikan.");
    }

    private function paymentNote(Transaction $transaction): string
    {
        if ((float) $transaction->change_amount > 0) {
            return ' Kembalian: Rp'.number_format((float) $transaction->change_amount, 0, ',', '.').'.';
        }

        if ($transaction->payment_status !== 'paid') {
            return ' Sisa tagihan: Rp'.number_format($transaction->due_amount, 0, ',', '.').'.';
        }

        return '';
    }

    /**
     * @return array{customers: Collection<int, Customer>, products: Collection<int, Product>}
     */
    private function formData(?Transaction $transaction = null): array
    {
        $reserved = $transaction
            ? $transaction->products->mapWithKeys(fn (Product $product) => [$product->id => (int) $product->pivot->quantity])
            : collect();

        $products = Product::query()
            ->orderBy('name')
            ->get()
            ->each(fn (Product $product) => $product->available_stock = $product->stock + $reserved->get($product->id, 0))
            ->filter(fn (Product $product) => $product->available_stock > 0)
            ->values();

        return [
            'customers' => Customer::query()->orderBy('name')->get(),
            'products' => $products,
        ];
    }
}
