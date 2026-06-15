<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProviderDashboardController extends Controller
{

     // الحصول على إحصائيات المتجر (المبيعات، الطلبات النشطة، عدد المنتجات، التقييم)

    public function stats(Request $request)
    {
        $provider = $request->user()->provider;

        if (!$provider) {
            return response()->json(['message' => 'ليس لديك متجر مسجل'], 404);
        }

        $totalSales = Order::where('provider_id', $provider->id)
            ->whereHas('status', function ($q) {
                $q->where('name', 'completed');
            })->sum('total_price');

        $activeOrdersCount = Order::where('provider_id', $provider->id)
            ->whereHas('status', function ($q) {
                $q->whereIn('name', ['pending', 'accepted', 'on_the_way']);
            })->count();

        $totalProducts = Product::where('provider_id', $provider->id)->count();

        $avgRating = Review::where('provider_id', $provider->id)->avg('rating') ?? 0;

        return response()->json([
            'total_sales' => (float) $totalSales,
            'active_orders_count' => $activeOrdersCount,
            'total_products' => $totalProducts,
            'avg_rating' => round($avgRating, 1),
        ]);
    }


    // آخر 5 طلبات للمتجر

    public function recentOrders(Request $request)
    {
        $providerId = $request->user()->provider->id;
        $orders = Order::with(['user', 'status', 'products'])
            ->where('provider_id', $providerId)
            ->latest()
            ->limit(5)
            ->get();

        // إضافة رابط الصورة لكل منتج
        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $product->image_path = $product->image_path
                    ? asset('storage/' . $product->image_path)
                    : null;
            }
        }

        return response()->json($orders);
    }

    // جميع طلبات المتجر (مع إمكانية البحث والفلترة)

    public function allOrders(Request $request)
    {
        $providerId = $request->user()->provider->id;
        $orders = Order::with(['user', 'status', 'products'])
            ->where('provider_id', $providerId)
            ->latest()
            ->get();

        // إضافة رابط الصورة لكل منتج
        foreach ($orders as $order) {
            foreach ($order->products as $product) {
                $product->image_path = $product->image_path
                    ? asset('storage/' . $product->image_path)
                    : null;
            }
        }

        return response()->json($orders);
    }
}
