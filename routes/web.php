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

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/test-order', function () {
    return view('test-order');
})->middleware('auth');

Route::resource('users', UserController::class);

Route::put('/users/{id}/restore', [UserController::class, 'restore'])->name('users.restore');

Route::get('/kitchen', [KitchenOrderItemController::class, 'index'])->middleware('auth');

Route::post(
    '/kitchen/{kitchenOrder}/start',
    [KitchenOrderItemController::class, 'start']
)->middleware(['auth', 'cook']);

Route::post(
    '/kitchen/{kitchenOrder}/complete',
    [KitchenOrderItemController::class, 'complete']
)->middleware(['auth', 'cook']);

Route::post(
    '/orders/{order}/complete',
    [OrderController::class, 'completeOrder']
)->middleware(['auth', 'cook']);

Route::get('/pos', [POSController::class, 'index'])
    ->name('pos.index')
    ->middleware('auth');

Route::get('/inventory', [InventoryItemController::class, 'index'])
    ->name('inventory.index');
Route::post(
    '/inventory/{inventoryItem}/toggle-active',
    [InventoryItemController::class, 'toggleActive']
)
    ->middleware('auth')
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
    ->middleware('auth')
    ->name('inventory.stock-in');

Route::post('/inventory/stock-in', [InventoryItemController::class, 'storeStockIn'])
    ->middleware('auth')
    ->name('inventory.stock-in.store');

Route::patch('/inventory/stocks/{stock}/quantity', [InventoryItemController::class, 'updateStockQuantity'])
    ->middleware('auth')
    ->name('inventory.update-stock-quantity');

Route::get('/finance-report', [FinanceReportController::class, 'index'])
    ->middleware('auth')
    ->name('finance-report.index');

Route::post('/inventory/daily', [InventoryItemController::class, 'storeDailyInventory'])
    ->name('inventory.daily.store');

Route::get('/inventory/daily-history', [InventoryItemController::class, 'dailyHistory'])
    ->name('inventory.daily-history');

Route::get('/inventory/non-countable-history', [InventoryItemController::class, 'nonCountableHistory'])
    ->name('inventory.non-countable-history');

Route::middleware('auth')->group(function () {
    Route::get('/purchases', [PurchaseController::class, 'index'])
        ->name('purchases.index');

    Route::post('/purchases', [PurchaseController::class, 'store'])
        ->name('purchases.store');
});

Route::get('/suppliers', [SupplierController::class, 'index'])
    ->name('suppliers.index');

Route::post('/suppliers', [SupplierController::class, 'store'])
    ->name('suppliers.store');

Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])
    ->name('suppliers.update');

Route::post('/suppliers/{supplier}/toggle-active', [SupplierController::class, 'toggleActive'])
    ->name('suppliers.toggle-active');

require __DIR__ . '/auth.php';
