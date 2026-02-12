<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SeedDemoInvoices extends Command
{
    protected $signature = 'demo:seed-invoices
        {--from=2026-02-01 : Fecha inicial (YYYY-MM-DD)}
        {--to= : Fecha final (YYYY-MM-DD). Por defecto: hoy}
        {--per-day=10 : Facturas por día}
        {--min-items=1 : Mínimo de productos por factura}
        {--max-items=4 : Máximo de productos por factura}
        {--min-qty=1 : Mínima cantidad por producto}
        {--max-qty=3 : Máxima cantidad por producto}';

    protected $description = 'Inserta facturas demo (invoices + invoice_items + sold_products) usando productos existentes, para un rango de fechas.';

    public function handle(): int
    {
        $fromStr = (string) $this->option('from');
        $toStr = (string) ($this->option('to') ?: Carbon::now()->format('Y-m-d'));

        $perDay = max(1, min(200, (int) $this->option('per-day')));
        $minItems = max(1, min(20, (int) $this->option('min-items')));
        $maxItems = max($minItems, min(50, (int) $this->option('max-items')));
        $minQty = max(1, min(1000, (int) $this->option('min-qty')));
        $maxQty = max($minQty, min(5000, (int) $this->option('max-qty')));

        try {
            $from = Carbon::createFromFormat('Y-m-d', $fromStr)->startOfDay();
            $to = Carbon::createFromFormat('Y-m-d', $toStr)->startOfDay();
        } catch (\Throwable $e) {
            $this->error('Formato de fechas inválido. Usa YYYY-MM-DD.');
            return self::FAILURE;
        }

        if ($from->gt($to)) {
            $this->error('La fecha inicial no puede ser mayor que la final.');
            return self::FAILURE;
        }

        $products = DB::table('products')->select(['id', 'price', 'stock'])->get();
        if ($products->isEmpty()) {
            $this->error('No hay productos en la tabla products. Crea productos antes de generar facturas.');
            return self::FAILURE;
        }

        $priceById = $products->pluck('price', 'id');

        $totalInvoices = 0;
        $skippedInvoices = 0;
        $totalItems = 0;

        $this->info("Generando {$perDay} facturas por día desde {$from->format('Y-m-d')} hasta {$to->format('Y-m-d')}...");
        $this->info('Este comando SOLO usa productos con stock y descuenta stock (ventas reales).');

        $day = $from->copy();
        while ($day->lte($to)) {
            $this->line('Día: ' . $day->format('Y-m-d'));

            for ($i = 1; $i <= $perDay; $i++) {
                $invoiceDate = $day->copy()->setTime(
                    random_int(8, 23),
                    random_int(0, 59),
                    random_int(0, 59)
                );

                $invoiceNumber = 'INV-' . $invoiceDate->format('Ymd') . '-' . Str::upper(Str::random(6)) . '-' . str_pad((string) $i, 2, '0', STR_PAD_LEFT);

                $now = $invoiceDate->copy();

                $created = DB::transaction(function () use (
                    $invoiceNumber,
                    $invoiceDate,
                    $now,
                    $minItems,
                    $maxItems,
                    $minQty,
                    $maxQty,
                    $priceById,
                    &$totalInvoices,
                    &$totalItems
                ) {
                    $available = DB::table('products')
                        ->select(['id', 'price', 'stock'])
                        ->where('stock', '>', 0)
                        ->lockForUpdate()
                        ->get();

                    if ($available->count() < $minItems) {
                        return false;
                    }

                    $itemsCount = random_int($minItems, $maxItems);
                    $itemsCount = min($itemsCount, $available->count());

                    $picked = $available->shuffle()->take($itemsCount)->values();

                    $invoiceItemsRows = [];
                    $soldProductsRows = [];
                    $stockUpdates = [];
                    $total = 0.0;

                    foreach ($picked as $p) {
                        $productId = (int) $p->id;
                        $stock = (int) ($p->stock ?? 0);
                        if ($stock < $minQty) {
                            continue;
                        }

                        $qty = random_int($minQty, min($maxQty, $stock));
                        $price = (float) ($priceById[$productId] ?? $p->price ?? 0);

                        $subtotal = round($price * $qty, 2);
                        $total += $subtotal;

                        $invoiceItemsRows[] = [
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'price' => $price,
                            'subtotal' => $subtotal,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $soldProductsRows[] = [
                            'invoice_number' => $invoiceNumber,
                            'product_id' => $productId,
                            'quantity' => $qty,
                            'price_total' => $subtotal,
                            'invoice_date' => $invoiceDate,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];

                        $stockUpdates[$productId] = $stock - $qty;
                    }

                    if (count($invoiceItemsRows) < 1) {
                        return false;
                    }

                    $invoiceId = DB::table('invoices')->insertGetId([
                        'invoice_number' => $invoiceNumber,
                        'total' => round($total, 2),
                        'status' => 'completed',
                        'notes' => null,
                        'invoice_date' => $invoiceDate,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);

                    foreach ($invoiceItemsRows as &$row) {
                        $row['invoice_id'] = $invoiceId;
                    }

                    foreach ($soldProductsRows as &$row) {
                        $row['invoice_id'] = $invoiceId;
                    }

                    DB::table('invoice_items')->insert($invoiceItemsRows);
                    DB::table('sold_products')->insert($soldProductsRows);

                    foreach ($stockUpdates as $productId => $newStock) {
                        DB::table('products')->where('id', $productId)->update([
                            'stock' => max(0, (int) $newStock),
                            'updated_at' => $now,
                        ]);
                    }

                    $totalInvoices += 1;
                    $totalItems += count($invoiceItemsRows);
                    return true;
                });

                if (!$created) {
                    $skippedInvoices += 1;
                }
            }

            $day->addDay();
        }

        $this->info("Listo. Facturas creadas: {$totalInvoices}. Facturas omitidas (sin stock suficiente): {$skippedInvoices}. Items creados: {$totalItems}.");
        return self::SUCCESS;
    }
}
