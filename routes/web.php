<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('home', [
        'usersCount' => User::query()->count(),
        'productsCount' => Product::query()->count(),
        'customersCount' => Customer::query()->count(),
        'transactionsCount' => Transaction::query()->count(),
        'revenue' => (float) Transaction::query()->sum('total'),
    ]);
})->name('home');

Route::get('/tes', function () {
    return view('tes');
});

Route::resource('users', UserController::class)->except(['show']);
Route::resource('products', ProductController::class)->except(['show']);
Route::resource('customers', CustomerController::class)->except(['show']);
Route::resource('transactions', TransactionController::class);
