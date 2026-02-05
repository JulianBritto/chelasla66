<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    public function topProducts(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 5);
        $limit = max(1, min($limit, 50));

        $rows = DB::table('sold_products as sp')
            ->join('products as p', 'p.id', '=', 'sp.product_id')
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(sp.quantity) as total_quantity'),
                DB::raw('SUM(sp.price_total) as total_sales'),
            ])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_quantity')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        $result = $rows->map(function ($r) {
            return [
                'product_id' => (int) $r->product_id,
                'product_name' => $r->product_name,
                'total_quantity' => (int) $r->total_quantity,
                'total_sales' => (float) $r->total_sales,
            ];
        });

        return response()->json($result);
    }

    public function topDays(Request $request): JsonResponse
    {
        $limit = (int) $request->query('limit', 4);
        $limit = max(1, min($limit, 31));

        $topProductsLimit = (int) $request->query('topProducts', 3);
        $topProductsLimit = max(1, min($topProductsLimit, 10));

        // We use invoices.created_at to represent the real "fecha de creación" of the invoice.
        $days = DB::table('sold_products as sp')
            ->join('invoices as i', 'i.id', '=', 'sp.invoice_id')
            ->select([
                DB::raw('DATE(i.created_at) as day'),
                DB::raw('SUM(sp.quantity) as total_quantity'),
                DB::raw('SUM(sp.price_total) as total_sales'),
            ])
            ->groupBy(DB::raw('DATE(i.created_at)'))
            ->orderByDesc('total_quantity')
            ->orderByDesc('total_sales')
            ->limit($limit)
            ->get();

        $result = $days->map(function ($d) use ($topProductsLimit) {
            $topProducts = DB::table('sold_products as sp')
                ->join('invoices as i', 'i.id', '=', 'sp.invoice_id')
                ->join('products as p', 'p.id', '=', 'sp.product_id')
                ->whereDate('i.created_at', $d->day)
                ->select([
                    'p.id as product_id',
                    'p.name as product_name',
                    DB::raw('SUM(sp.quantity) as quantity'),
                    DB::raw('SUM(sp.price_total) as total_sales'),
                ])
                ->groupBy('p.id', 'p.name')
                ->orderByDesc('quantity')
                ->orderByDesc('total_sales')
                ->limit($topProductsLimit)
                ->get()
                ->map(function ($p) {
                    return [
                        'product_id' => (int) $p->product_id,
                        'product_name' => $p->product_name,
                        'quantity' => (int) $p->quantity,
                        'total_sales' => (float) $p->total_sales,
                    ];
                })
                ->values();

            return [
                'date' => (string) $d->day, // YYYY-MM-DD
                'total_quantity' => (int) $d->total_quantity,
                'total_sales' => (float) $d->total_sales,
                'top_products' => $topProducts,
            ];
        })->values();

        return response()->json($result);
    }

    public function getUnifiedStatistics(): JsonResponse
    {
        $last30Days = [];

        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last30Days[$date->format('Y-m-d')] = [
                'date' => $date->format('d/m'),
                'products_sold' => 0,
                'invoices_count' => 0,
                'total_revenue' => 0,
            ];
        }

        $invoices = Invoice::where('invoice_date', '>=', Carbon::now()->subDays(30))
            ->with('items')
            ->get();

        foreach ($invoices as $invoice) {
            $dateKey = $invoice->invoice_date->format('Y-m-d');
            if (!isset($last30Days[$dateKey])) {
                continue;
            }

            $last30Days[$dateKey]['products_sold'] += (int) $invoice->items->sum('quantity');
            $last30Days[$dateKey]['invoices_count'] += 1;
            $last30Days[$dateKey]['total_revenue'] += (float) $invoice->total;
        }

        return response()->json(array_values($last30Days));
    }

    public function getDailySalesData(): JsonResponse
    {
        $last30Days = [];
        
        // Crear array con los últimos 30 días
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last30Days[$date->format('Y-m-d')] = [
                'date' => $date->format('d/m'),
                'sales' => 0,
                'revenue' => 0,
                'transactionCount' => 0,
            ];
        }

        // Obtener facturas de los últimos 30 días
        $invoices = Invoice::where('invoice_date', '>=', Carbon::now()->subDays(30))
            ->with('items')
            ->get();

        // Agrupar por fecha
        foreach ($invoices as $invoice) {
            $date = $invoice->invoice_date->format('Y-m-d');
            if (isset($last30Days[$date])) {
                $last30Days[$date]['sales'] += $invoice->items->sum('quantity');
                $last30Days[$date]['revenue'] += $invoice->total;
                $last30Days[$date]['transactionCount'] += 1;
            }
        }

        // Preparar datos para respuesta
        $dates = array_column($last30Days, 'date');
        $sales = array_column($last30Days, 'sales');
        $revenue = array_column($last30Days, 'revenue');
        $transactionCount = array_column($last30Days, 'transactionCount');

        return response()->json([
            'dates' => $dates,
            'sales' => $sales,
            'revenue' => $revenue,
            'transactionCount' => $transactionCount,
        ]);
    }

    public function getDailyRevenueData(): JsonResponse
    {
        $last30Days = [];
        
        // Crear array con los últimos 30 días
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $last30Days[$date->format('Y-m-d')] = [
                'date' => $date->format('d/m'),
                'revenue' => 0,
            ];
        }

        // Obtener facturas de los últimos 30 días
        $invoices = Invoice::where('invoice_date', '>=', Carbon::now()->subDays(30))->get();

        // Agrupar por fecha
        foreach ($invoices as $invoice) {
            $date = $invoice->invoice_date->format('Y-m-d');
            if (isset($last30Days[$date])) {
                $last30Days[$date]['revenue'] += $invoice->total;
            }
        }

        // Preparar datos para respuesta
        $dates = array_column($last30Days, 'date');
        $revenue = array_column($last30Days, 'revenue');

        return response()->json([
            'dates' => $dates,
            'revenue' => $revenue,
        ]);
    }

    public function getDailySalesByDateRange(): JsonResponse
    {
        $startDate = request('startDate');
        $endDate = request('endDate');

        if (!$startDate || !$endDate) {
            return response()->json(['error' => 'Se requieren fechas de inicio y fin'], 400);
        }

        $start = Carbon::parse($startDate);
        $end = Carbon::parse($endDate);
        $days = [];

        // Crear array con los días en el rango
        for ($date = $start; $date->lte($end); $date->addDay()) {
            $days[$date->format('Y-m-d')] = [
                'date' => $date->format('d/m/Y'),
                'transactionCount' => 0,
                'revenue' => 0,
            ];
        }

        // Obtener facturas en el rango
        $invoices = Invoice::whereBetween('invoice_date', [$start, $end->addDay()])
            ->with('items')
            ->get();

        // Agrupar por fecha
        foreach ($invoices as $invoice) {
            $date = $invoice->invoice_date->format('Y-m-d');
            if (isset($days[$date])) {
                $days[$date]['transactionCount'] += 1;
                $days[$date]['revenue'] += $invoice->total;
            }
        }

        return response()->json($days);
    }
}
