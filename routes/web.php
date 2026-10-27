<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Cashier\PosController;
use App\Http\Controllers\Warehouse\WarehouseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Root redirect
Route::get('/', function () {
    if (Auth::check()) {
        return redirect(Auth::user()->roleRedirectRoute());
    }
    return redirect()->route('login');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');

// Kasir Routes (Role: kasir)
Route::middleware(['auth', 'role:kasir'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/', [PosController::class, 'index'])->name('pos');
    Route::get('/products/search', [PosController::class, 'searchProduct'])->name('products.search');
    Route::post('/checkout', [PosController::class, 'checkout'])->name('checkout');
    Route::get('/receipt/{invoiceNumber}', [PosController::class, 'receipt'])->name('receipt');
    Route::get('/recent-sales', [PosController::class, 'recentSales'])->name('recent-sales');
});

// Gudang Routes (Role: gudang)
Route::middleware(['auth', 'role:gudang'])->prefix('warehouse')->name('warehouse.')->group(function () {
    Route::get('/', [WarehouseController::class, 'dashboard'])->name('dashboard');
    Route::get('/products', [WarehouseController::class, 'products'])->name('products.index');
    Route::post('/products', [WarehouseController::class, 'storeProduct'])->name('products.store');
    Route::put('/products/{id}', [WarehouseController::class, 'updateProduct'])->name('products.update');
    Route::post('/products/{id}/toggle-status', [WarehouseController::class, 'toggleProductStatus'])->name('products.toggle');
    Route::get('/receipts/create', [WarehouseController::class, 'goodsReceiptForm'])->name('receipts.create');
    Route::post('/receipts', [WarehouseController::class, 'storeGoodsReceipt'])->name('receipts.store');
    Route::get('/adjustments/create', [WarehouseController::class, 'adjustmentForm'])->name('adjustments.create');
    Route::post('/adjustments', [WarehouseController::class, 'storeAdjustment'])->name('adjustments.store');
    Route::get('/movements', [WarehouseController::class, 'movements'])->name('movements');
});

// Admin Routes (Role: admin) - Read-Only Only (No mutating routes)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/reports/sales', [AdminController::class, 'salesReport'])->name('reports.sales');
    Route::get('/reports/profit', [AdminController::class, 'profitReport'])->name('reports.profit');
    Route::get('/reports/inventory', [AdminController::class, 'inventoryReport'])->name('reports.inventory');
    Route::get('/reports/sales/{invoiceNumber}', [AdminController::class, 'saleDetail'])->name('reports.sale-detail');
});
