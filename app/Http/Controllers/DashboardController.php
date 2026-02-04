<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Invoice;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalProducts = Product::count();
        $totalStock = Product::sum('stock');
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::sum('total');
        
        $recentInvoices = Invoice::orderBy('created_at', 'desc')->take(5)->get();
        $lowStockProducts = Product::where('stock', '<', 5)->orderBy('stock', 'desc')->get();

        return view('dashboard', [
            'totalProducts' => $totalProducts,
            'totalStock' => $totalStock,
            'totalInvoices' => $totalInvoices,
            'totalRevenue' => $totalRevenue,
            'recentInvoices' => $recentInvoices,
            'lowStockProducts' => $lowStockProducts,
        ]);
    }
}
