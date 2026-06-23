<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

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

    //chang 6/1/2026


    //  إضافة منتج جديد (خاص بالتاجر المسجل دخوله)

    public function store(Request $request)
    {
        $request->validate([
            'type'        => 'required|string|max:255',
            'price'       => 'required|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(['message' => 'ليس لديك متجر'], 403);
        }

        $imagePath = null;
        if ($request->hasFile('image')) {
            // حفظ الصورة في مجلد providers داخل storage/app/public
            $imagePath = $request->file('image')->store('providers', 'public');
        }

        $product = Product::create([
            'provider_id'  => $provider->id,
            'type'         => $request->type,
            'price'        => $request->price,
            'description'  => $request->description,
            'image_path'   => $imagePath,
        ]);

        // إضافة رابط الصورة الكامل للرد
        $product->image_path = $product->image_path ? asset('storage/' . $product->image_path) : null;

        return response()->json($product, 201);
    }


    // تعديل منتج موجود

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $provider = $request->user()->provider;

        if (!$provider || $product->provider_id !== $provider->id) {
            return response()->json(['message' => 'غير مصرح لك بتعديل هذا المنتج'], 403);
        }

        $request->validate([
            'type'        => 'sometimes|string|max:255',
            'price'       => 'sometimes|numeric|min:0',
            'description' => 'nullable|string|max:255',
            'image'       => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا وجدت
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $imagePath = $request->file('image')->store('providers', 'public');
            $product->image_path = $imagePath;
        }

        if ($request->has('type')) $product->type = $request->type;
        if ($request->has('price')) $product->price = $request->price;
        if ($request->has('description')) $product->description = $request->description;

        $product->save();

        $product->image_path = $product->image_path ? asset('storage/' . $product->image_path) : null;
        return response()->json($product);
    }


    //  حذف منتج

    public function destroy(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $provider = $request->user()->provider;

        if (!$provider || $product->provider_id !== $provider->id) {
            return response()->json(['message' => 'غير مصرح بحذف هذا المنتج'], 403);
        }

        if ($product->image_path) {
            \Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
        return response()->json(['message' => 'تم حذف المنتج بنجاح']);
    }


    //  جلب منتجات المتجر الحالي (للتاجر نفسه)

    public function myProducts(Request $request)
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(['message' => 'ليس لديك متجر'], 404);
        }

        $products = Product::where('provider_id', $provider->id)->get();
        foreach ($products as $product) {
            if ($product->image_path) {
                // استبدال \ بـ / وتأكد من الرابط
                $cleanPath = str_replace('\\', '/', $product->image_path);
                $product->image_path = asset('storage/' . str_replace('storage/', '', $cleanPath));
            }

        // $product->image_path = $product->image_path ? asset('storage/' . $product->image_path) : null;
        }

        return response()->json($products);
    }

}
