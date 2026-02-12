<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class StatisticsController extends Controller
{
    private function buildDaySummary(string $date, int $topProductsLimit = 3): array
    {
        $topProductsLimit = max(1, min($topProductsLimit, 10));

        $totalsRow = DB::table('sold_products as sp')
            ->leftJoin('product_costs as pc', 'pc.product_id', '=', 'sp.product_id')
            ->where(function ($q) use ($date) {
                $q->whereDate('sp.invoice_date', $date)
                    ->orWhere(function ($q2) use ($date) {
                        $q2->whereNull('sp.invoice_date')->whereDate('sp.created_at', $date);
                    });
            })
            ->select([
                DB::raw('SUM(sp.quantity) as total_transactions'),
                DB::raw('SUM(sp.price_total) as total_sales'),
                DB::raw('SUM(sp.price_total) - SUM(sp.quantity * COALESCE(pc.purchase_price, 0)) as total_profit'),
            ])
            ->first();

        $topProducts = DB::table('sold_products as sp')
            ->join('products as p', 'p.id', '=', 'sp.product_id')
            ->leftJoin('product_costs as pc', 'pc.product_id', '=', 'sp.product_id')
            ->where(function ($q) use ($date) {
                $q->whereDate('sp.invoice_date', $date)
                    ->orWhere(function ($q2) use ($date) {
                        $q2->whereNull('sp.invoice_date')->whereDate('sp.created_at', $date);
                    });
            })
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(sp.quantity) as total_quantity'),
                DB::raw('SUM(sp.price_total) as total_sales'),
                DB::raw('SUM(sp.price_total) - SUM(sp.quantity * COALESCE(pc.purchase_price, 0)) as total_profit'),
            ])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_quantity')
            ->orderByDesc('total_sales')
            ->limit($topProductsLimit)
            ->get()
            ->map(function ($r) {
                return [
                    'product_id' => (int) $r->product_id,
                    'product_name' => (string) $r->product_name,
                    'total_quantity' => (int) ($r->total_quantity ?? 0),
                    'total_sales' => (float) ($r->total_sales ?? 0),
                    'total_profit' => (float) ($r->total_profit ?? 0),
                ];
            })
            ->values();

        return [
            'date' => $date,
            'total_transactions' => (int) ($totalsRow->total_transactions ?? 0),
            'total_sales' => (float) ($totalsRow->total_sales ?? 0),
            'total_profit' => (float) ($totalsRow->total_profit ?? 0),
            'top_products' => $topProducts,
        ];
    }

    private function buildRangeSummary(string $fromDate, string $toDate, int $topProductsLimit = 3): array
    {
        $topProductsLimit = max(1, min($topProductsLimit, 10));

        $totalsRow = DB::table('sold_products as sp')
            ->leftJoin('product_costs as pc', 'pc.product_id', '=', 'sp.product_id')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->where(function ($q1) use ($fromDate, $toDate) {
                    $q1->whereDate('sp.invoice_date', '>=', $fromDate)
                        ->whereDate('sp.invoice_date', '<=', $toDate);
                })
                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
                        $q2->whereNull('sp.invoice_date')
                            ->whereDate('sp.created_at', '>=', $fromDate)
                            ->whereDate('sp.created_at', '<=', $toDate);
                    });
            })
            ->select([
                DB::raw('SUM(sp.quantity) as total_transactions'),
                DB::raw('SUM(sp.price_total) as total_sales'),
                DB::raw('SUM(sp.price_total) - SUM(sp.quantity * COALESCE(pc.purchase_price, 0)) as total_profit'),
            ])
            ->first();

        $topProducts = DB::table('sold_products as sp')
            ->join('products as p', 'p.id', '=', 'sp.product_id')
            ->leftJoin('product_costs as pc', 'pc.product_id', '=', 'sp.product_id')
            ->where(function ($q) use ($fromDate, $toDate) {
                $q->where(function ($q1) use ($fromDate, $toDate) {
                    $q1->whereDate('sp.invoice_date', '>=', $fromDate)
                        ->whereDate('sp.invoice_date', '<=', $toDate);
                })
                    ->orWhere(function ($q2) use ($fromDate, $toDate) {
                        $q2->whereNull('sp.invoice_date')
                            ->whereDate('sp.created_at', '>=', $fromDate)
                            ->whereDate('sp.created_at', '<=', $toDate);
                    });
            })
            ->select([
                'p.id as product_id',
                'p.name as product_name',
                DB::raw('SUM(sp.quantity) as total_quantity'),
                DB::raw('SUM(sp.price_total) as total_sales'),
                DB::raw('SUM(sp.price_total) - SUM(sp.quantity * COALESCE(pc.purchase_price, 0)) as total_profit'),
            ])
            ->groupBy('p.id', 'p.name')
            ->orderByDesc('total_quantity')
            ->orderByDesc('total_sales')
            ->limit($topProductsLimit)
            ->get()
            ->map(function ($r) {
                return [
                    'product_id' => (int) $r->product_id,
                    'product_name' => (string) $r->product_name,
                    'total_quantity' => (int) ($r->total_quantity ?? 0),
                    'total_sales' => (float) ($r->total_sales ?? 0),
                    'total_profit' => (float) ($r->total_profit ?? 0),
                ];
            })
            ->values();

        return [
            'from' => $fromDate,
            'to' => $toDate,
            'label' => $fromDate . ' al ' . $toDate,
            'total_transactions' => (int) ($totalsRow->total_transactions ?? 0),
            'total_sales' => (float) ($totalsRow->total_sales ?? 0),
            'total_profit' => (float) ($totalsRow->total_profit ?? 0),
            'top_products' => $topProducts,
        ];
    }

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
            ->leftJoin('product_costs as pc', 'pc.product_id', '=', 'sp.product_id')
            ->select([
                DB::raw('DATE(i.created_at) as day'),
                DB::raw('SUM(sp.quantity) as total_quantity'),
                DB::raw('SUM(sp.price_total) as total_sales'),
                DB::raw('SUM(sp.price_total) - SUM(sp.quantity * COALESCE(pc.purchase_price, 0)) as total_profit'),
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
                'total_profit' => (float) ($d->total_profit ?? 0),
                'top_products' => $topProducts,
            ];
        })->values();

        return response()->json($result);
    }

    public function dayCompare(Request $request): JsonResponse
    {
        $startDate = (string) $request->query('startDate', '');
        $endDate = (string) $request->query('endDate', '');

        if (!$startDate || !$endDate) {
            return response()->json(['message' => 'Se requieren startDate y endDate (YYYY-MM-DD)'], 422);
        }

        // Validación simple de formato YYYY-MM-DD
        $isStartValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate) === 1;
        $isEndValid = preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate) === 1;

        if (!$isStartValid || !$isEndValid) {
            return response()->json(['message' => 'Formato inválido. Usa YYYY-MM-DD.'], 422);
        }

        $topLimit = (int) $request->query('topProducts', 3);
        $topLimit = max(1, min($topLimit, 10));

        return response()->json([
            'startDate' => $startDate,
            'endDate' => $endDate,
            'start' => $this->buildDaySummary($startDate, $topLimit),
            'end' => $this->buildDaySummary($endDate, $topLimit),
        ]);
    }

    public function weekCompare(Request $request): JsonResponse
    {
        $startFrom = (string) $request->query('startFrom', '');
        $startTo = (string) $request->query('startTo', '');
        $endFrom = (string) $request->query('endFrom', '');
        $endTo = (string) $request->query('endTo', '');

        if (!$startFrom || !$startTo || !$endFrom || !$endTo) {
            return response()->json(['message' => 'Se requieren startFrom, startTo, endFrom, endTo (YYYY-MM-DD)'], 422);
        }

        $isValid = function (string $d): bool {
            return preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1;
        };

        if (!$isValid($startFrom) || !$isValid($startTo) || !$isValid($endFrom) || !$isValid($endTo)) {
            return response()->json(['message' => 'Formato inválido. Usa YYYY-MM-DD.'], 422);
        }

        try {
            $sf = Carbon::createFromFormat('Y-m-d', $startFrom)->startOfDay();
            $st = Carbon::createFromFormat('Y-m-d', $startTo)->startOfDay();
            $ef = Carbon::createFromFormat('Y-m-d', $endFrom)->startOfDay();
            $et = Carbon::createFromFormat('Y-m-d', $endTo)->startOfDay();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Fechas inválidas.'], 422);
        }

        if ($sf->gt($st)) {
            return response()->json(['message' => 'En el rango inicio, la fecha inicial no puede ser mayor que la final.'], 422);
        }

        if ($ef->gt($et)) {
            return response()->json(['message' => 'En el rango fin, la fecha inicial no puede ser mayor que la final.'], 422);
        }

        $topLimit = (int) $request->query('topProducts', 3);
        $topLimit = max(1, min($topLimit, 10));

        return response()->json([
            'startFrom' => $startFrom,
            'startTo' => $startTo,
            'endFrom' => $endFrom,
            'endTo' => $endTo,
            'start' => $this->buildRangeSummary($startFrom, $startTo, $topLimit),
            'end' => $this->buildRangeSummary($endFrom, $endTo, $topLimit),
        ]);
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
