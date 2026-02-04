<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductCost;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProductController extends Controller
{
    public function getAll(): JsonResponse
    {
        $products = Product::with('cost')->get();
        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);
        return response()->json($product, 201);
    }

    public function update(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string',
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);

        $product->update($validated);
        return response()->json($product);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }

    public function updateCost(Request $request, Product $product): JsonResponse
    {
        $validated = $request->validate([
            'purchase_price' => 'required|numeric|min:0',
            'profit' => 'required|numeric|min:0',
        ]);

        $cost = $product->cost ?: new ProductCost();
        $cost->product_id = $product->id;
        $cost->purchase_price = $validated['purchase_price'];
        $cost->profit = $validated['profit'];
        $cost->save();

        return response()->json($cost);
    }
}
