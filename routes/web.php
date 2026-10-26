<?php
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\ExpiredProductController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;
  use App\Http\Controllers\DailySalesController;

Route::get('/', function () {
    return redirect()->route('products.index');
});
Route::get('/penjualan', [DailySalesController::class, 'index'])->name('sales.daily');
Route::get('expired', ExpiredProductController::class)->name('expired.index');
Route::get('/laporan', [ReportController::class, 'profit'])->name('reports.profit');
Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');
Route::resource('products', ProductController::class)->except('show');
Route::resource('customers', CustomerController::class)->except('show');
Route::resource('suppliers', SupplierController::class)->except('show');
Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show']);
Route::patch('orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.tracking');
Route::patch('orders/{order}/shipping', [OrderController::class, 'updateShipping'])->name('orders.shipping');
Route::patch('orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('orders.payment');