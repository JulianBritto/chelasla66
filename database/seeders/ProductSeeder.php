<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Cerveza Artesanal IPA',
                'description' => 'Cerveza artesanal India Pale Ale, 5.8% alcohol',
                'price' => 8500,
                'stock' => 150,
            ],
            [
                'name' => 'Cerveza Lager Premium',
                'description' => 'Cerveza lager tradicional, 4.5% alcohol',
                'price' => 7500,
                'stock' => 200,
            ],
            [
                'name' => 'Bebida Energética Tropical',
                'description' => 'Bebida energética sabor tropical, 250ml',
                'price' => 5500,
                'stock' => 300,
            ],
            [
                'name' => 'Agua Mineral Gasificada',
                'description' => 'Agua mineral con gas, 500ml',
                'price' => 3500,
                'stock' => 500,
            ],
            [
                'name' => 'Jugo Natural Naranja',
                'description' => 'Jugo natural de naranja 100%, 1L',
                'price' => 9500,
                'stock' => 120,
            ],
            [
                'name' => 'Refresco Cola Clásico',
                'description' => 'Refresco cola sabor clásico, 2L',
                'price' => 6500,
                'stock' => 250,
            ],
            [
                'name' => 'Té Helado Limón',
                'description' => 'Té helado sabor limón, 500ml',
                'price' => 4500,
                'stock' => 180,
            ],
            [
                'name' => 'Bebida de Manzana Premium',
                'description' => 'Bebida de manzana con vitaminas, 1L',
                'price' => 8000,
                'stock' => 160,
            ],
            [
                'name' => 'Agua de Coco Natural',
                'description' => 'Agua de coco natural 100%, 1L',
                'price' => 7000,
                'stock' => 100,
            ],
            [
                'name' => 'Bebida Isotónica Deportiva',
                'description' => 'Bebida isotónica para deportistas, 500ml',
                'price' => 6000,
                'stock' => 220,
            ]
        ];

        foreach ($products as $product) {
            Product::create($product);
        }
    }
}
