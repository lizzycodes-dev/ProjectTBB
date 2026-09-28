<?php

use App\Http\Controllers\KitchenOrderItemController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\POSController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\RecipeItemsController;
use App\Http\Controllers\FinanceReportController;

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

Route::get('/inventory', [InventoryItemController::class, 'index'])
    ->name('inventory.index');
Route::post(
    '/inventory/{inventoryItem}/toggle-active',
    [InventoryItemController::class, 'toggleActive']
)
    ->middleware('auth')
    ->name('inventory.toggle-active');

<<<<<<< HEAD
Route::patch('/inventory/{inventoryItem}/unit', [InventoryItemController::class, 'updateUnit'])
    ->name('inventory.update-unit');

Route::get('/inventory/stock-in', [InventoryItemController::class, 'createStockIn'])
    ->middleware('auth')
    ->name('inventory.stock-in');

Route::post('/inventory/stock-in', [InventoryItemController::class, 'storeStockIn'])
    ->middleware('auth')
    ->name('inventory.stock-in.store');

Route::patch('/inventory/stocks/{stock}/quantity', [InventoryItemController::class, 'updateStockQuantity'])
    ->middleware('auth')
    ->name('inventory.update-stock-quantity');
=======
Route::get('/finance-report', [FinanceReportController::class, 'index'])
    ->middleware('auth')
    ->name('finance-report.index');
>>>>>>> 79f85b0b6819a25456a32dbfe336cd9b0ca8649b

Route::get('/recipe-management', [RecipeItemsController::class, 'index'])
    ->name('recipe-management.index');

Route::post('/recipe-management', [RecipeItemsController::class, 'store'])
    ->name('recipe-management.store');

Route::post(
    '/recipe-management/adjustments',
    [RecipeItemsController::class, 'storeAdjustments']
)
    ->middleware('auth')
    ->name('recipe-management.adjustments');

require __DIR__ . '/auth.php';