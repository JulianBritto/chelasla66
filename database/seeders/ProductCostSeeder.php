<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCost;

class ProductCostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $costs = [
            ['product_id' => 1, 'purchase_price' => 3500, 'profit' => 5000],     // IPA: 8500 - 3500
            ['product_id' => 2, 'purchase_price' => 3000, 'profit' => 4500],     // Lager: 7500 - 3000
            ['product_id' => 3, 'purchase_price' => 2500, 'profit' => 3000],     // Energética: 5500 - 2500
            ['product_id' => 4, 'purchase_price' => 1500, 'profit' => 2000],     // Agua: 3500 - 1500
            ['product_id' => 5, 'purchase_price' => 4000, 'profit' => 5500],     // Jugo: 9500 - 4000
            ['product_id' => 6, 'purchase_price' => 2500, 'profit' => 4000],     // Cola: 6500 - 2500
            ['product_id' => 7, 'purchase_price' => 2000, 'profit' => 2500],     // Té: 4500 - 2000
            ['product_id' => 8, 'purchase_price' => 3500, 'profit' => 4500],     // Manzana: 8000 - 3500
            ['product_id' => 9, 'purchase_price' => 3000, 'profit' => 4000],     // Agua de Coco: 7000 - 3000
            ['product_id' => 10, 'purchase_price' => 2500, 'profit' => 3500],    // Isotónica: 6000 - 2500
        ];

        foreach ($costs as $cost) {
            ProductCost::create($cost);
        }
    }
}
