<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    //عشان ما انسى
    /**
     * POST /api/v1/reviews
     * تقييم الطلبية والسائق والمتجر معاً بعملية واحدة
     *
     * الحقول المتوقعة:
     *   order_id          required
     *   rating            required  1-5  (تقييم عام / للمتجر)
     *   comment           nullable
     *   driver_rating     nullable  1-5  (تقييم السائق بشكل منفصل)
     *   driver_comment    nullable
     */
    public function store(Request $request)
    {
        $request->validate([
            'order_id'      => 'required|exists:orders,id',
            'rating'        => 'required|integer|min:1|max:5',
            'comment'       => 'nullable|string|max:1000',
            'driver_rating' => 'nullable|integer|min:1|max:5',
            'driver_comment' => 'nullable|string|max:1000',
        ]);

        $order = Order::with(['provider', 'driver'])->findOrFail($request->order_id);

        // التأكد أن الطلب يخص المستخدم الحالي
        if ($order->user_id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        // لا تسمح بتقييم مرتين
        if ($order->review) {
            return response()->json(['message' => 'تم التقييم مسبقاً'], 422);
        }

        // حساب التقييم النهائي: إذا وُجد driver_rating نعمل متوسط، وإلا نستخدم rating مباشرة
        $finalRating = $request->driver_rating
            ? round(($request->rating + $request->driver_rating) / 2)
            : $request->rating;

        $review = Review::create([
            'user_id'     => $request->user()->id,
            'order_id'    => $order->id,
            'provider_id' => $order->provider_id ?? null,
            'rating'      => $finalRating,
            'comment'     => $request->comment ?? $request->driver_comment ?? '',

            // ممكن اضيفها على ال review مبدئيا (not yet)
            // 'provider_rating' => $request->rating,
            // 'driver_rating'   => $request->driver_rating,
        ]);

        return response()->json([
            'message' => 'تم إضافة التقييم بنجاح',
            'data'    => $review,
        ], 201);
    }


     //GET /api/v1/provider/reviews
    // جلب تقييمات المتجر الحالي (للتاجر)

    public function providerReviews(Request $request)
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(['message' => 'ليس لديك متجر'], 404);
        }

        $reviews = Review::with('user')
            ->where('provider_id', $provider->id)
            ->latest()
            ->get();

        return response()->json($reviews);
    }


    //  GET /api/v1/provider/rating
    //  متوسط تقييم المتجر

    public function providerRating(Request $request)
    {
        $provider = $request->user()->provider;
        if (!$provider) {
            return response()->json(['message' => 'ليس لديك متجر'], 404);
        }

        $avg = Review::where('provider_id', $provider->id)->avg('rating') ?? 0;
        return response()->json(['avg_rating' => round($avg, 1)]);
    }


      //GET /api/v1/driver/reviews
     // تقييمات السائق الحالي

    public function driverReviews(Request $request)
    {
        $driver = $request->user()->driver;
        if (!$driver) {
            return response()->json(['message' => 'ليس لديك سجل سائق'], 404);
        }

        $reviews = Review::with(['user', 'order'])
            ->whereHas('order', function ($q) use ($driver) {
                $q->where('driver_id', $driver->id);
            })
            ->latest()
            ->get();

        return response()->json($reviews);
    }
}
