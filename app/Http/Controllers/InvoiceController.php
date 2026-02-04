<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\SoldProduct;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InvoiceController extends Controller
{
    public function getAll(): JsonResponse
    {
        $invoices = Invoice::with('items.product')->orderBy('created_at', 'desc')->get();
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

            // Registrar en sold_products (una fila por producto vendido)
            SoldProduct::create([
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'product_id' => $item['product_id'],
                'quantity' => $item['quantity'],
                'price_total' => $item['price'] * $item['quantity'],
                'invoice_date' => $invoice->invoice_date,
            ]);
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

        // Eliminar registros en sold_products relacionados
        SoldProduct::where('invoice_id', $invoice->id)->orWhere('invoice_number', $invoice->invoice_number)->delete();

        $invoice->delete();
        return response()->json(['message' => 'Invoice deleted successfully']);
    }

        public function update(Request $request, Invoice $invoice)
        {
            $validated = $request->validate([
                'items' => ['required', 'array', 'min:1'],
                'items.*.product_id' => ['required', 'integer', 'exists:products,id'],
                'items.*.quantity' => ['required', 'integer', 'min:1'],
            ]);

            $items = collect($validated['items'])
                ->map(function ($item) {
                    return [
                        'product_id' => (int) $item['product_id'],
                        'quantity' => (int) $item['quantity'],
                    ];
                })
                ->values();

            $duplicateProductIds = $items->pluck('product_id')->duplicates();
            if ($duplicateProductIds->isNotEmpty()) {
                return response()->json([
                    'message' => 'No se permiten productos duplicados en la factura.',
                ], 422);
            }

            return DB::transaction(function () use ($invoice, $items) {
                $invoice->load('items');

                $now = now();

                $oldQuantities = $invoice->items
                    ->groupBy('product_id')
                    ->map(fn($group) => (int) $group->sum('quantity'));

                $newQuantities = $items
                    ->keyBy('product_id')
                    ->map(fn($i) => (int) $i['quantity']);

                if ($oldQuantities->count() === $newQuantities->count()) {
                    $same = true;
                    foreach ($newQuantities as $productId => $qty) {
                        if (!$oldQuantities->has($productId) || (int) $oldQuantities[$productId] !== (int) $qty) {
                            $same = false;
                            break;
                        }
                    }

                    if ($same) {
                        return response()->json([
                            'message' => 'No hubo cambios en la factura',
                            'invoice' => $invoice->fresh('items.product'),
                        ]);
                    }
                }

                $productIds = $oldQuantities->keys()
                    ->merge($newQuantities->keys())
                    ->unique()
                    ->values();

                $products = Product::whereIn('id', $productIds)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                $availableStock = [];
                foreach ($productIds as $productId) {
                    $product = $products->get($productId);
                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => ['Producto no encontrado.'],
                        ]);
                    }

                    $availableStock[$productId] = (int) $product->stock + (int) ($oldQuantities[$productId] ?? 0);
                }

                $total = 0;
                $invoiceItemRows = [];
                $soldProductRows = [];

                foreach ($items as $item) {
                    $productId = (int) $item['product_id'];
                    $quantity = (int) $item['quantity'];
                    $product = $products->get($productId);

                    if ($availableStock[$productId] < $quantity) {
                        throw ValidationException::withMessages([
                            'items' => ["Stock insuficiente para {$product->name}. Disponible: {$availableStock[$productId]}."],
                        ]);
                    }

                    $availableStock[$productId] -= $quantity;

                    $price = (float) $product->price;
                    $subtotal = round($price * $quantity, 2);
                    $total += $subtotal;

                    $invoiceItemRows[] = [
                        'invoice_id' => $invoice->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    $soldProductRows[] = [
                        'invoice_id' => $invoice->id,
                        'invoice_number' => $invoice->invoice_number,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                        'price_total' => $subtotal,
                        'invoice_date' => $invoice->invoice_date ?? $invoice->created_at,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                foreach ($productIds as $productId) {
                    $product = $products->get($productId);
                    $product->stock = $availableStock[$productId];
                    $product->save();
                }

                InvoiceItem::where('invoice_id', $invoice->id)->delete();
                InvoiceItem::insert($invoiceItemRows);

                $invoice->update(['total' => $total]);

                $existingSold = SoldProduct::where('invoice_id', $invoice->id)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('product_id');

                $newProductIds = collect($soldProductRows)->pluck('product_id')->values();

                foreach ($soldProductRows as $row) {
                    $productId = (int) $row['product_id'];
                    $newQuantity = (int) $row['quantity'];
                    $newPriceTotal = round((float) $row['price_total'], 2);
                    $newPriceTotalStr = number_format($newPriceTotal, 2, '.', '');

                    /** @var SoldProduct|null $sold */
                    $sold = $existingSold->get($productId);
                    if (!$sold) {
                        SoldProduct::create($row);
                        continue;
                    }

                    $currentQuantity = (int) $sold->quantity;
                    $currentPriceTotalStr = number_format(round((float) $sold->price_total, 2), 2, '.', '');

                    if ($currentQuantity !== $newQuantity || $currentPriceTotalStr !== $newPriceTotalStr) {
                        $sold->quantity = $newQuantity;
                        $sold->price_total = $newPriceTotal;
                        $sold->save();
                    }
                }

                // Delete removed products (these were removed from the invoice)
                SoldProduct::where('invoice_id', $invoice->id)
                    ->whereNotIn('product_id', $newProductIds)
                    ->delete();

                return response()->json([
                    'message' => 'Factura actualizada correctamente',
                    'invoice' => $invoice->fresh('items.product'),
                ]);
            });
        }
}
