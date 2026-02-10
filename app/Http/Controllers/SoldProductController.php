<?php

namespace App\Http\Controllers;

use App\Models\SoldProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SoldProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $date = $request->query('date');
        $limit = (int) $request->query('limit');

        $query = SoldProduct::with(['product', 'invoice']);

        if ($date) {
            $query->where(function ($q) use ($date) {
                $q->whereDate('invoice_date', $date)
                    ->orWhere(function ($q2) use ($date) {
                        $q2->whereNull('invoice_date')->whereDate('created_at', $date);
                    });
            });
        }

        $query->orderBy('invoice_date', 'desc')->orderBy('created_at', 'desc');

        if ($limit > 0) {
            $limit = min($limit, 100);
            $query->limit($limit);
        }

        $sold = $query->get();

        // Map to a simple structure
        $result = $sold->map(function ($s) {
            return [
                'id' => $s->id,
                'invoice_id' => $s->invoice_id,
                'invoice_number' => $s->invoice_number,
                'invoice_created_at' => $s->invoice ? $s->invoice->created_at : null,
                'product_id' => $s->product_id,
                'product_name' => $s->product ? $s->product->name : null,
                'quantity' => $s->quantity,
                'price_total' => (float) $s->price_total,
                'invoice_date' => $s->invoice_date ?? $s->created_at,
                'created_at' => $s->created_at,
                'updated_at' => $s->updated_at,
            ];
        });

        return response()->json($result);
    }
}
