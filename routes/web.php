<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceTicketPdfController;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::middleware('guest')->group(function () {
	Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
	Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
});

Route::post('/logout', [AuthController::class, 'logout'])
	->middleware('auth')
	->name('logout');

Route::middleware('auth')->group(function () {
	Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

	Route::get('/invoices/{invoice}/ticket', InvoiceTicketPdfController::class)
		->name('invoices.ticket');
});

