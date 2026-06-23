<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Provider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProviderController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Provider::with('user');

            // ── بحث بالاسم أو النوع ──────────────────────────────
            if ($request->has('search') && $request->search) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('type', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($u) use ($search) {
                          $u->where('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                      });
                });
            }

            // ── فلتر حسب النوع ───────────────────────────────────────
            if ($request->has('type') && $request->type) {
                $query->where('type', 'like', '%' . $request->type . '%');
            }

            // ── فلتر حسب المدينة/العنوان ────────────────────────────
            if ($request->has('city') && $request->city) {
                $query->whereHas('user', function ($u) use ($request) {
                    $u->where('address', 'like', '%' . $request->city . '%');
                });
            }

            // ── فلتر حسب وجود منتجات ────────────────────────────────
            if ($request->has('has_products')) {
                if ($request->has_products == 'true' || $request->has_products == 1) {
                    $query->has('products', '>', 0);
                } else {
                    $query->has('products', '=', 0);
                }
            }

            // ── ترتيب النتائج ────────────────────────────────────────
            $sortBy = $request->sort_by ?? 'created_at';
            $sortOrder = $request->sort_order ?? 'desc';

            $allowedSorts = ['id', 'type', 'created_at', 'updated_at'];
            if (in_array($sortBy, $allowedSorts)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // ── جلب النتائج مع pagination أو بدون ──────────────────
            if ($request->has('per_page')) {
                $perPage = $request->per_page > 0 ? $request->per_page : 10;
                $providers = $query->paginate($perPage);
            } else {
                $providers = $query->get();
            }

            // ✅ إضافة رابط الصورة لكل متجر
            $providers->each(function ($provider) {
                if ($provider->user && $provider->user->image_path) {
                    $provider->user->image_url = asset('storage/' . $provider->user->image_path);
                } else {
                    $provider->user->image_url = null;
                }
            });

            // ✅ إضافة إحصائيات إضافية للـ frontend
            $types = Provider::distinct()->pluck('type')->filter()->values();

            return response()->json([
                'status' => true,
                'message' => 'تم جلب المتاجر بنجاح',
                'data' => $providers,
                'stats' => [
                    'total' => $providers instanceof \Illuminate\Pagination\LengthAwarePaginator ? $providers->total() : $providers->count(),
                    'types' => $types
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error fetching providers: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب المتاجر: ' . $e->getMessage()
            ], 500);
        }
    }
    // جلب منتجات المتجر
    public function byProvider($providerId)
    {
        try {
            $products = Product::where('provider_id', $providerId)->get();

            //  إضافة رابط الصورة
            $products->each(function ($product) {
                if ($product->image_path) {
                    $product->image_url = asset('storage/' . $product->image_path);
                }
            });

            return response()->json([
                'status' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching products: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب المنتجات'
            ], 500);
        }
    }

    // جلب منتجاتي (للمتجر)
    public function myProducts(Request $request)
    {
        try {
            $provider = $request->user()->provider;
            if (!$provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'لم يتم العثور على متجر'
                ], 404);
            }

            $products = Product::where('provider_id', $provider->id)->get();

            //  إضافة رابط الصورة
            $products->each(function ($product) {
                if ($product->image_path) {
                    $product->image_url = asset('storage/' . $product->image_path);
                }
            });

            return response()->json([
                'status' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching my products: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب المنتجات'
            ], 500);
        }
    }

    //  إضافة منتج جديد مع صورة
    public function store(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $provider = $request->user()->provider;
            if (!$provider) {
                return response()->json([
                    'status' => false,
                    'message' => 'لم يتم العثور على متجر'
                ], 404);
            }

            $product = new Product();
            $product->provider_id = $provider->id;
            $product->type = $request->type;
            $product->price = $request->price;
            $product->description = $request->description;

            //  معالجة الصورة - تخزين في products/
            if ($request->hasFile('image')) {
                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // تخزين في storage/app/public/products
                $path = $image->storeAs('products', $imageName, 'public');

                if ($path) {
                    //  حفظ المسار في قاعدة البيانات بصيغة products/اسم_الصورة
                    $product->image_path = $path;
                }
            }

            $product->save();

            //  إضافة رابط الصورة للرد
            $product->image_url = $product->image_path ? asset('storage/' . $product->image_path) : null;

            return response()->json([
                'status' => true,
                'message' => 'تم إضافة المنتج بنجاح',
                'data' => $product
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating product: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في إضافة المنتج: ' . $e->getMessage()
            ], 500);
        }
    }

    //  تحديث منتج مع صورة
    public function update(Request $request, $id)
    {
        try {
            $request->validate([
                'type' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'description' => 'nullable|string|max:255',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048'
            ]);

            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'المنتج غير موجود'
                ], 404);
            }

            $provider = $request->user()->provider;
            if (!$provider || $product->provider_id !== $provider->id) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح لك بتعديل هذا المنتج'
                ], 403);
            }

            $product->type = $request->type;
            $product->price = $request->price;
            $product->description = $request->description;

            //  معالجة الصورة الجديدة
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة
                if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                    Storage::disk('public')->delete($product->image_path);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // تخزين في storage/app/public/products
                $path = $image->storeAs('products', $imageName, 'public');

                if ($path) {
                    $product->image_path = $path;
                }
            }

            $product->save();

            //  إضافة رابط الصورة للرد
            $product->image_url = $product->image_path ? asset('storage/' . $product->image_path) : null;

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث المنتج بنجاح',
                'data' => $product
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating product: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث المنتج: ' . $e->getMessage()
            ], 500);
        }
    }

    //  حذف منتج مع الصورة
    public function destroy($id)
    {
        try {
            $product = Product::find($id);
            if (!$product) {
                return response()->json([
                    'status' => false,
                    'message' => 'المنتج غير موجود'
                ], 404);
            }

            // حذف الصورة من التخزين
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }

            $product->delete();

            return response()->json([
                'status' => true,
                'message' => 'تم حذف المنتج بنجاح'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting product: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في حذف المنتج'
            ], 500);
        }
    }
}
