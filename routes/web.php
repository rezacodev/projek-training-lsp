<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\UserController;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Route;

// Rute Otentikasi (Login / Logout)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Rute Terproteksi Otentikasi (Memerlukan Login)
Route::middleware('auth')->group(function () {
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
});
