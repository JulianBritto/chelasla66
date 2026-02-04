<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class InvoiceController extends Controller
{
    public function getAll(): JsonResponse
    {
        $invoices = Invoice::with('items.product')->get();
        return response()->json($invoices);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items' => 'required|array',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $invoiceNumber = 'INV-' . date('YmdHis');
        $total = 0;

        // Calcular el total
        foreach ($validated['items'] as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'total' => $total,
            'invoice_date' => now(),
            'notes' => $validated['notes'] ?? null,
            'status' => 'completed'
        ]);

        // Crear items de factura
        foreach ($validated['items'] as $item) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price' => $item['price'],
                'subtotal' => $item['price'] * $item['quantity'],
            ]);

            // Actualizar stock
            $product = Product::find($item['product_id']);
            $product->stock -= $item['quantity'];
            $product->save();
        }

        return response()->json($invoice, 201);
    }

    public function show(Invoice $invoice): JsonResponse
    {
        return response()->json($invoice->load('items.product'));
    }

    public function destroy(Invoice $invoice): JsonResponse
    {
        // Restaurar stock
        foreach ($invoice->items as $item) {
            $product = Product::find($item->product_id);
            $product->stock += $item->quantity;
            $product->save();
        }

        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully']);
    }
}
