<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;


class ProductController extends Controller
{
    // $path = $request->file('image')->store('products', 'public');
    public function getProducts($providerId, Request $request)
    {
        $query = Product::where('provider_id', $providerId);

        // filter by type
        if ($request->type) {
            $query->where('type', $request->type);
        }

        // price filters
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }

        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // sorting
        if ($request->sort === 'name') {
            $query->orderBy('name', 'asc');
        } elseif ($request->sort === 'price') {
            $query->orderBy('price', 'asc');
        } else {
            $query->orderBy('id', 'desc');
        }

        $products = $query->get();

        // ADD IMAGE URL
        foreach ($products as $product) {
            $product->image_path = $product->image_path
                ? asset(str_replace('\\', '/', 'storage/' . $product->image_path))
                : null;
        }
        return response()->json([
            'status' => true,
            'data' => $products
        ]);
    }


    //  GET /api/v1/providers/{provider}/products
   //   جلب جميع منتجات مزود معيّن

    public function byProvider(Provider $provider): AnonymousResourceCollection
    {
        $products = $provider->products()->latest()->get();

        return ProductResource::collection($products);
    }

}
