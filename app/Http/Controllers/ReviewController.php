<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Provider;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\ProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Order;
use App\Models\Review;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReviewController extends Controller
{


    public function store(Request $request)
{
    $request->validate([
        'order_id' => 'required|exists:orders,id',
        'rating'   => 'required|integer|min:1|max:5',
        'comment'  => 'nullable|string',
    ]);

    $order = Order::findOrFail($request->order_id);

    // 🔥 مهم جدًا: التأكد أن الطلب يخص المستخدم
    if ($order->user_id !== $request->user()->id) {
        return response()->json(['message' => 'غير مصرح'], 403);
    }

    // 🔥 لا تسمح بتقييم مرتين
    if ($order->review) {
        return response()->json(['message' => 'تم التقييم مسبقاً'], 422);
    }

    $review = Review::create([
        'user_id'  => $request->user()->id,
        'order_id' => $order->id,
        'rating'   => $request->rating,
        'comment'  => $request->comment,
    ]);

    return response()->json([
        'message' => 'تم إضافة التقييم',
        'data' => $review
    ]);
}
}
