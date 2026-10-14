<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

Route::resource('products', ProductController::class)->except('show');
Route::resource('customers', CustomerController::class)->except('show');
Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
Route::patch('orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.tracking');
Route::patch('orders/{order}/shipping', [OrderController::class, 'updateShipping'])->name('orders.shipping');
Route::patch('orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('orders.payment');