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

// Product Routes
Route::get('/products', [ProductController::class, 'getAll']);
Route::post('/products', [ProductController::class, 'store']);
Route::put('/products/{product}', [ProductController::class, 'update']);
Route::delete('/products/{product}', [ProductController::class, 'destroy']);
Route::post('/products/{product}/cost', [ProductController::class, 'updateCost']);

// Invoice Routes
Route::get('/invoices', [InvoiceController::class, 'getAll']);
Route::post('/invoices', [InvoiceController::class, 'store']);
Route::get('/invoices/{invoice}', [InvoiceController::class, 'show']);
Route::put('/invoices/{invoice}', [InvoiceController::class, 'update']);
Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy']);

// Statistics Routes
Route::get('/statistics/daily-sales', [StatisticsController::class, 'getDailySalesData']);
Route::get('/statistics/daily-revenue', [StatisticsController::class, 'getDailyRevenueData']);
Route::get('/statistics/daily-sales-by-range', [StatisticsController::class, 'getDailySalesByDateRange']);
Route::get('/statistics/unified', [StatisticsController::class, 'getUnifiedStatistics']);

// Sold products (for daily close view)
Route::get('/sold-products', [SoldProductController::class, 'index']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
