<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReviewRequest;
use App\Models\Order;
use App\Models\Review;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * قائمة جميع التقييمات
     */
    public function index(): JsonResponse
    {
        $reviews = Review::with(['user', 'order.provider.user'])->latest()->get();

        return response()->json($reviews);
    }

    /**
     * إضافة تقييم جديد
     */
    public function store(ReviewRequest $request): JsonResponse
    {
        $order = Order::findOrFail($request->order_id);

        // التحقق من عدم وجود تقييم مسبق لهذا الطلب من نفس المستخدم
        if ($order->review) {
            return response()->json(['message' => 'This order already has a review'], 422);
        }

        $review = Review::create([
            'user_id'  => $request->user_id,
            'order_id' => $request->order_id,
            'rating'   => $request->rating,
            'comment'  => $request->comment,
        ]);

        return response()->json([
            'message' => 'Review added successfully',
            'review'  => $review->load(['user', 'order']),
        ], 201);
    }

    /**
     * عرض تقييم محدد
     */
    public function show($id): JsonResponse
    {
        $review = Review::with(['user', 'order.provider.user'])->findOrFail($id);

        return response()->json($review);
    }

    /**
     * تحديث تقييم
     */
    public function update(ReviewRequest $request, $id): JsonResponse
    {
        $review = Review::findOrFail($id);
        $review->update($request->only(['rating', 'comment']));

        return response()->json([
            'message' => 'Review updated successfully',
            'review'  => $review->load(['user', 'order']),
        ]);
    }

    /**
     * حذف تقييم
     */
    public function destroy($id): JsonResponse
    {
        Review::findOrFail($id)->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }

    /**
     * تقييمات مقدم خدمة معين (عبر طلباته)
     */
    public function providerReviews($providerId): JsonResponse
    {
        $reviews = Review::with(['user', 'order'])
                         ->whereHas('order', fn ($q) => $q->where('provider_id', $providerId))
                         ->latest()
                         ->get();

        return response()->json($reviews);
    }
}
