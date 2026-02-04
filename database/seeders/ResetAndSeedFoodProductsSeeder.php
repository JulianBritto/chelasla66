<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Product;

class ResetAndSeedFoodProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks for MySQL
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Truncate tables that hold transactional data
        DB::table('invoice_items')->truncate();
        DB::table('sold_products')->truncate();
        DB::table('invoices')->truncate();
        DB::table('product_costs')->truncate();
        DB::table('products')->truncate();

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $products = [
            ['name' => 'Arepa de Choclo', 'description' => 'Arepa de choclo tradicional', 'price' => 8000, 'stock' => 100],
            ['name' => 'Empanada de Carne', 'description' => 'Empanada rellena de carne, frita', 'price' => 3500, 'stock' => 200],
            ['name' => 'Taco de Pollo', 'description' => 'Taco con pollo sazonado y salsa', 'price' => 7000, 'stock' => 120],
            ['name' => 'Hamburguesa Clásica', 'description' => 'Hamburguesa con queso y papas', 'price' => 15000, 'stock' => 80],
            ['name' => 'Porción de Papas Fritas', 'description' => 'Papas fritas crujientes', 'price' => 6000, 'stock' => 180],
            ['name' => 'Pollo a la Brasa (1/4)', 'description' => 'Pollo a la brasa porción', 'price' => 18000, 'stock' => 60],
            ['name' => 'Ensalada César', 'description' => 'Ensalada César con pollo', 'price' => 12000, 'stock' => 90],
            ['name' => 'Sándwich Club', 'description' => 'Sándwich club con pollo y tocino', 'price' => 14000, 'stock' => 70],
            ['name' => 'Pizza Personal', 'description' => 'Pizza individual con queso y pepperoni', 'price' => 13000, 'stock' => 75],
            ['name' => 'Nachos con Queso', 'description' => 'Nachos cubiertos con queso y jalapeños', 'price' => 9000, 'stock' => 110],
        ];

        foreach ($products as $p) {
            Product::create($p);
        }
    }
}
