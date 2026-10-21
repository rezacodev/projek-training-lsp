<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('customers.index', [
            'customers' => Customer::query()->withCount('transactions')->latest()->paginate(10),
        ]);
    }

    public function create(): View
    {
        return view('customers.create');
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        $customer = Customer::create($request->validated());

        return to_route('customers.index')
            ->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    public function edit(Customer $customer): View
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('customers.index')
            ->with('success', "Data pelanggan {$customer->name} berhasil diperbarui.");
    }

    public function destroy(Customer $customer): RedirectResponse
    {
        if ($customer->transactions()->exists()) {
            return to_route('customers.index')
                ->with('error', "Pelanggan {$customer->name} tidak dapat dihapus karena sudah memiliki transaksi.");
        }

        $customer->delete();

        return to_route('customers.index')
            ->with('success', "Pelanggan {$customer->name} berhasil dihapus.");
    }
}
