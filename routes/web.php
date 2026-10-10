<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KitchenOrderItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SupplierController;

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Everyone logged in (cook, cashier, manager)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Kitchen: everyone can view it (cashiers view-only)
    Route::get('/kitchen', [KitchenOrderItemController::class, 'index'])
        ->name('kitchen.index');

    // Kitchen actions: cooks AND managers only
    Route::post('/kitchen/{kitchenOrder}/start', [KitchenOrderItemController::class, 'start'])
        ->middleware('no.cashier');
    Route::post('/kitchen/{kitchenOrder}/complete', [KitchenOrderItemController::class, 'complete'])
        ->middleware('no.cashier');
    Route::post('/orders/{order}/complete', [OrderController::class, 'completeOrder'])
        ->middleware('no.cashier');

    // Inventory: cooks AND managers (cashiers are sent back to the POS)
    Route::middleware('no.cashier')->group(function () {
        Route::get('/inventory', [InventoryItemController::class, 'index'])
            ->name('inventory.index');

        Route::post('/inventory/{inventoryItem}/toggle-active', [InventoryItemController::class, 'toggleActive'])
            ->name('inventory.toggle-active');

        Route::get('/inventory/create', [InventoryItemController::class, 'create'])
            ->name('inventory.create');

        Route::post('/inventory', [InventoryItemController::class, 'store'])
            ->name('inventory.store');

        Route::get('/inventory/{inventoryItem}/edit', [InventoryItemController::class, 'edit'])
            ->name('inventory.edit');

        Route::put('/inventory/{inventoryItem}', [InventoryItemController::class, 'update'])
            ->name('inventory.update');

        Route::get('/inventory/stock-in', [InventoryItemController::class, 'createStockIn'])
            ->name('inventory.stock-in');

        Route::post('/inventory/stock-in', [InventoryItemController::class, 'storeStockIn'])
            ->name('inventory.stock-in.store');

        Route::patch('/inventory/stocks/{stock}/quantity', [InventoryItemController::class, 'updateStockQuantity'])
            ->name('inventory.update-stock-quantity');

        Route::post('/inventory/daily', [InventoryItemController::class, 'storeDailyInventory'])
            ->name('inventory.daily.store');

        Route::get('/inventory/daily-history', [InventoryItemController::class, 'dailyHistory'])
            ->name('inventory.daily-history');

        Route::get('/inventory/non-countable-history', [InventoryItemController::class, 'nonCountableHistory'])
            ->name('inventory.non-countable-history');

        Route::get('/inventory/sales-history', [InventoryItemController::class, 'salesHistory'])
            ->name('inventory.sales-history');
    });
});

/*
|--------------------------------------------------------------------------
| Dashboard: managers only (cooks go to /kitchen, cashiers to /pos)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'no.cook', 'no.cashier'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
});

Route::middleware(['auth', 'no.cook'])->group(function () {
    Route::post('/orders', [OrderController::class, 'store']);

    Route::get('/test-order', function () {
        return view('test-order');
    });

    Route::get('/pos', [POSController::class, 'index'])
        ->name('pos.index');
});

/*
|--------------------------------------------------------------------------
| Manager only (blocked for cooks and cashiers)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'no.cook', 'no.cashier'])->group(function () {
    Route::get('/finance-report', [FinanceReportController::class, 'index'])
        ->name('finance-report.index');

    Route::resource('users', UserController::class);
    Route::put('/users/{id}/restore', [UserController::class, 'restore'])
        ->name('users.restore');

    Route::get('/purchases', [PurchaseController::class, 'index'])
        ->name('purchases.index');

    Route::post('/purchases', [PurchaseController::class, 'store'])
        ->name('purchases.store');

    Route::get('/suppliers', [SupplierController::class, 'index'])
        ->name('suppliers.index');

    Route::post('/suppliers', [SupplierController::class, 'store'])
        ->name('suppliers.store');

    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
        ->name('suppliers.update');

    Route::post('/suppliers/{supplier}/toggle-active', [SupplierController::class, 'toggleActive'])
        ->name('suppliers.toggle-active');
});

require __DIR__ . '/auth.php';
