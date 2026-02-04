<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RandomInvoicesForDatesSeeder extends Seeder
{
    public function run(): void
    {
        $dates = [
            '2026-02-01' => 5,
            '2026-02-02' => 5,
            '2026-02-03' => 5,
        ];

        $allProducts = DB::table('products')->select(['id', 'name', 'price', 'stock'])->get();
        if ($allProducts->isEmpty()) {
            $this->command?->warn('No hay productos en la base de datos. Ejecuta ProductSeeder primero.');
            return;
        }

        foreach ($dates as $date => $desiredCount) {
            $existingCount = (int) DB::table('invoices')->whereDate('created_at', $date)->count();
            $countToCreate = max(0, $desiredCount - $existingCount);

            if ($countToCreate === 0) {
                $this->command?->info("↷ Ya existen {$existingCount} facturas en {$date}. No se crean nuevas.");
                continue;
            }

            $this->command?->info("+ Creando {$countToCreate} facturas para {$date}...");

            for ($i = 0; $i < $countToCreate; $i++) {
                DB::transaction(function () use ($date, $i) {
                    // Random time within the day
                    $dt = Carbon::parse($date)
                        ->setHour(random_int(8, 22))
                        ->setMinute(random_int(0, 59))
                        ->setSecond(random_int(0, 59));

                    // Pick between 1 and 4 items
                    $itemsCount = random_int(1, 4);

                    // Only choose products with stock > 0 (fresh within the transaction)
                    $available = DB::table('products')
                        ->where('stock', '>', 0)
                        ->inRandomOrder()
                        ->limit(20)
                        ->get(['id', 'name', 'price', 'stock']);

                    if ($available->isEmpty()) {
                        $this->command?->warn("Sin stock disponible para crear factura en {$date}.");
                        return;
                    }

                    $chosen = $available->take(min($itemsCount, $available->count()));

                    $invoiceNumber = 'INV-' . $dt->format('YmdHis') . '-' . str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) . '-' . Str::upper(Str::random(2));

                    $invoiceId = DB::table('invoices')->insertGetId([
                        'invoice_number' => $invoiceNumber,
                        'total' => 0,
                        'status' => 'completed',
                        'notes' => 'Factura generada automáticamente (seed)',
                        'invoice_date' => $dt,
                        'created_at' => $dt,
                        'updated_at' => $dt,
                    ]);

                    $invoiceItems = [];
                    $soldProducts = [];
                    $total = 0;

                    foreach ($chosen as $product) {
                        $maxQty = (int) min(3, (int) $product->stock);
                        if ($maxQty < 1) {
                            continue;
                        }

                        $qty = random_int(1, $maxQty);
                        $price = (float) $product->price;
                        $subtotal = round($price * $qty, 2);
                        $total += $subtotal;

                        // Decrement stock
                        DB::table('products')->where('id', $product->id)->decrement('stock', $qty);

                        $invoiceItems[] = [
                            'invoice_id' => $invoiceId,
                            'product_id' => $product->id,
                            'quantity' => $qty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'created_at' => $dt,
                            'updated_at' => $dt,
                        ];

                        $soldProducts[] = [
                            'invoice_id' => $invoiceId,
                            'invoice_number' => $invoiceNumber,
                            'product_id' => $product->id,
                            'quantity' => $qty,
                            'price_total' => $subtotal,
                            'invoice_date' => $dt,
                            'created_at' => $dt,
                            'updated_at' => $dt,
                        ];
                    }

                    if (empty($invoiceItems)) {
                        // If nothing could be added, remove invoice record
                        DB::table('invoices')->where('id', $invoiceId)->delete();
                        $this->command?->warn("No se pudo crear items para {$invoiceNumber} (sin stock suficiente).");
                        return;
                    }

                    DB::table('invoice_items')->insert($invoiceItems);
                    DB::table('sold_products')->insert($soldProducts);

                    DB::table('invoices')->where('id', $invoiceId)->update([
                        'total' => round($total, 2),
                    ]);
                });
            }
        }

        $this->command?->info('✓ Seeder de facturas aleatorias finalizado.');
    }
}
