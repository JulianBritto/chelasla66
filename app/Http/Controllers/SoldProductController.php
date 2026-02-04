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

        $query = SoldProduct::with('product');

        if ($date) {
            $query->whereDate('created_at', $date);
        }

        $sold = $query->orderBy('created_at', 'desc')->get();

        // Map to a simple structure
        $result = $sold->map(function ($s) {
            return [
                'id' => $s->id,
                'invoice_id' => $s->invoice_id,
                'invoice_number' => $s->invoice_number,
                'product_id' => $s->product_id,
                'product_name' => $s->product ? $s->product->name : null,
                'quantity' => $s->quantity,
                'price_total' => (float) $s->price_total,
                'invoice_date' => $s->invoice_date ?? $s->created_at,
                'created_at' => $s->created_at,
            ];
        });

        return response()->json($result);
    }
}
