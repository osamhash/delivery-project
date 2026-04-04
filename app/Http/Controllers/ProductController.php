<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductRequest;
use App\Models\Product;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::with('provider.user')->get();
        return response()->json($products);
    }

    public function show($id)
    {
        $product = Product::with('provider.user')->findOrFail($id);
        return response()->json($product);
    }

    public function store(ProductRequest $request)
    {
        $product = Product::create($request->validated());
        return response()->json([
            'message' => 'Product created successfully',
            'product' => $product->load('provider.user'),
        ], 201);
    }

    public function update(ProductRequest $request, $id)
    {
        $product = Product::findOrFail($id);
        $product->update($request->validated());
        return response()->json([
            'message' => 'Product updated successfully',
            'product' => $product->load('provider.user'),
        ]);
    }

    public function destroy($id)
    {
        Product::findOrFail($id)->delete();
        return response()->json(['message' => 'Product deleted successfully']);
    }
}