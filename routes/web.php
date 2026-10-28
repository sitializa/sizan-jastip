<?php
use App\Http\Controllers\ArchiveController;
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
use App\Http\Controllers\ProductRecapController;

Route::get('/', function () {
    return redirect()->route('products.index');
});
Route::get('/penjualan', [DailySalesController::class, 'index'])->name('sales.daily');
Route::get('expired', ExpiredProductController::class)->name('expired.index');
Route::get('/laporan', [ReportController::class, 'profit'])->name('reports.profit');
Route::get('/customers/{customer}/invoice', [InvoiceController::class, 'customer'])->name('customers.invoice');
Route::get('/orders/{order}/invoice', [InvoiceController::class, 'show'])->name('orders.invoice');
Route::resource('products', ProductController::class)->except('show');
Route::resource('customers', CustomerController::class)->except('show');
Route::resource('suppliers', SupplierController::class)->except('show');
Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'show']);
Route::resource('orders', OrderController::class)->only(['index', 'create', 'store', 'show', 'destroy']);
Route::patch('orders/{order}/tracking', [OrderController::class, 'updateTracking'])->name('orders.tracking');
Route::patch('orders/{order}/shipping', [OrderController::class, 'updateShipping'])->name('orders.shipping');
  Route::get('/rekap-produk', [ProductRecapController::class, 'index'])->name('recap.products');
Route::patch('orders/{order}/payment', [OrderController::class, 'updatePayment'])->name('orders.payment');
  Route::get('/arsip', [ArchiveController::class, 'index'])->name('archive.index');
  Route::post('/arsip/{id}/pulihkan', [ArchiveController::class, 'restore'])->name('archive.restore');
  Route::delete('/arsip/{id}', [ArchiveController::class, 'destroy'])->name('archive.destroy');