<?php

use App\Http\Controllers\KitchenOrderItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\FinanceReportController;
use App\Http\Controllers\DailySheetController;
use App\Http\Controllers\SpoilageController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/orders', [OrderController::class, 'store']);
Route::get('/test-order', function () {
    return view('test-order');
})->middleware('auth');

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

Route::get('/pos', [POSController::class, 'index'])->middleware('auth');

Route::middleware('auth')->prefix('inventory')->name('inventory.')->group(function () {
    // Page + tabs (?tab=sheet|stock|spoilage|suppliers)
    Route::get('/', [InventoryItemController::class, 'index'])->name('index');

    // Stock Management
    Route::post('/{inventoryItem}/toggle-active', [InventoryItemController::class, 'toggleActive'])
        ->name('toggle-active');
    Route::patch('/{inventoryItem}/unit', [InventoryItemController::class, 'updateUnit'])
        ->name('update-unit');
    Route::patch('/{inventoryItem}/details', [InventoryItemController::class, 'updateDetails'])
        ->name('update-details');
    Route::get('/stock-in', [InventoryItemController::class, 'createStockIn'])->name('stock-in');
    Route::post('/stock-in', [InventoryItemController::class, 'storeStockIn'])->name('stock-in.store');
    Route::patch('/stocks/{stock}/quantity', [InventoryItemController::class, 'updateStockQuantity'])
        ->name('update-stock-quantity');

    // Daily Sheet
    Route::post('/sheet/{sheet}/use-current', [DailySheetController::class, 'useCurrentStock'])
        ->name('sheet.use-current');
    Route::post('/sheet/{sheet}/beginning', [DailySheetController::class, 'saveBeginning'])
        ->name('sheet.beginning');
    Route::post('/sheet/{sheet}/ending', [DailySheetController::class, 'saveEnding'])
        ->name('sheet.ending');
    Route::post('/sheet/{sheet}/close', [DailySheetController::class, 'close'])
        ->name('sheet.close');
    Route::post('/sheet/{sheet}/reset', [DailySheetController::class, 'reset'])
        ->name('sheet.reset');

    // Spoilage Log
    Route::post('/spoilage', [SpoilageController::class, 'store'])->name('spoilage.store');

    // Suppliers & deliveries (manager only, enforced in the controller)
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    Route::post('/suppliers/deliveries', [SupplierController::class, 'storeDelivery'])
        ->name('suppliers.deliveries.store');
});

Route::get('/finance-report', [FinanceReportController::class, 'index'])
    ->middleware('auth')
    ->name('finance-report.index');

// User management (creates users.index, users.create, users.store, users.edit, etc.)
Route::middleware('auth')->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
});

require __DIR__ . '/auth.php';