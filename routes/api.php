<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\SoldProductController;
use App\Http\Controllers\StatisticsController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware(['web', 'auth'])->group(function () {
    // Product Routes
    Route::get('/products', [ProductController::class, 'getAll']);
    Route::post('/products', [ProductController::class, 'store'])->middleware('role:1');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('role:1');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('role:1');
    Route::post('/products/{product}/cost', [ProductController::class, 'updateCost'])->middleware('role:1');

    // Invoice Routes
    Route::get('/invoices', [InvoiceController::class, 'getAll']);
    Route::post('/invoices', [InvoiceController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->middleware('role:1');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->middleware('role:1');

    // Sold products (for dashboard + daily close)
    Route::get('/sold-products', [SoldProductController::class, 'index']);

    // Statistics Routes (admin only)
    Route::get('/statistics/daily-sales', [StatisticsController::class, 'getDailySalesData'])->middleware('role:1');
    Route::get('/statistics/daily-revenue', [StatisticsController::class, 'getDailyRevenueData'])->middleware('role:1');
    Route::get('/statistics/daily-sales-by-range', [StatisticsController::class, 'getDailySalesByDateRange'])->middleware('role:1');
    Route::get('/statistics/unified', [StatisticsController::class, 'getUnifiedStatistics'])->middleware('role:1');
    Route::get('/statistics/top-products', [StatisticsController::class, 'topProducts'])->middleware('role:1');
    Route::get('/statistics/top-days', [StatisticsController::class, 'topDays'])->middleware('role:1');
    Route::get('/statistics/day-compare', [StatisticsController::class, 'dayCompare'])->middleware('role:1');
    Route::get('/statistics/week-compare', [StatisticsController::class, 'weekCompare'])->middleware('role:1');
});

Route::middleware(['web', 'auth'])->get('/user', function (Request $request) {
    return $request->user();
});
