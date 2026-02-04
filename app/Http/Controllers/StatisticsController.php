<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class StatisticsController extends Controller
{
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
