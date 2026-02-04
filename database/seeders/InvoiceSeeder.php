<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use Carbon\Carbon;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = Product::all();

        // Crear 10 facturas del 2026-02-03
        for ($i = 0; $i < 10; $i++) {
            $invoiceDate = Carbon::parse('2026-02-03')->setHour(rand(8, 20))->setMinute(rand(0, 59));
            
            $invoice = Invoice::create([
                'invoice_number' => 'INV-20260203-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'total' => 0,
                'status' => 'completed',
                'notes' => 'Factura de prueba',
                'invoice_date' => $invoiceDate
            ]);

            // Usar cada producto para una factura diferente
            $product = $products[$i];
            $quantity = rand(1, 5);
            $price = $product->price;
            $subtotal = $quantity * $price;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal
            ]);

            // Descontar del stock
            $product->decrement('stock', $quantity);

            // Actualizar total de la factura
            $invoice->update(['total' => $subtotal]);
        }

        // Crear 10 facturas del 2026-02-02
        for ($i = 0; $i < 10; $i++) {
            $invoiceDate = Carbon::parse('2026-02-02')->setHour(rand(8, 20))->setMinute(rand(0, 59));
            
            $invoice = Invoice::create([
                'invoice_number' => 'INV-20260202-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'total' => 0,
                'status' => 'completed',
                'notes' => 'Factura de prueba',
                'invoice_date' => $invoiceDate
            ]);

            // Usar cada producto para una factura diferente
            $product = $products[$i];
            $quantity = rand(1, 5);
            $price = $product->price;
            $subtotal = $quantity * $price;

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'product_id' => $product->id,
                'quantity' => $quantity,
                'price' => $price,
                'subtotal' => $subtotal
            ]);

            // Descontar del stock
            $product->decrement('stock', $quantity);

            // Actualizar total de la factura
            $invoice->update(['total' => $subtotal]);
        }
    }
}
