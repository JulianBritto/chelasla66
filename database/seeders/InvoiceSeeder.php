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

        // Crear facturas para los últimos 30 días
        for ($day = 30; $day >= 0; $day--) {
            $invoiceDate = Carbon::now()->subDays($day)->setHour(rand(8, 20))->setMinute(rand(0, 59));
            
            // 2-4 facturas por día
            $invoicesPerDay = rand(2, 4);
            
            for ($i = 0; $i < $invoicesPerDay; $i++) {
                $invoice = Invoice::create([
                    'invoice_number' => 'INV-' . $invoiceDate->format('YmdHis') . '-' . ($i + 1),
                    'total' => 0,
                    'status' => 'completed',
                    'notes' => 'Factura de prueba',
                    'invoice_date' => $invoiceDate
                ]);

                // 2-5 items por factura
                $itemCount = rand(2, 5);
                $total = 0;
                $selectedProducts = $products->random(min($itemCount, $products->count()));

                foreach ($selectedProducts as $product) {
                    $quantity = rand(1, 10);
                    $price = $product->price;
                    $subtotal = $quantity * $price;
                    $total += $subtotal;

                    InvoiceItem::create([
                        'invoice_id' => $invoice->id,
                        'product_id' => $product->id,
                        'quantity' => $quantity,
                        'price' => $price,
                        'subtotal' => $subtotal
                    ]);

                    // Descontar del stock
                    $product->decrement('stock', $quantity);
                }

                // Actualizar total de la factura
                $invoice->update(['total' => $total]);
            }
        }
    }
}
