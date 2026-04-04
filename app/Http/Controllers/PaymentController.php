<?php

namespace App\Http\Controllers;

use App\Http\Requests\PaymentRequest;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;

class PaymentController extends Controller
{
    
    public function index(): JsonResponse
    {
        $payments = Payment::with(['user', 'order', 'driver.user'])->latest()->get();

        return response()->json($payments);
    }

    
    public function store(PaymentRequest $request): JsonResponse
    {
        $order = Order::findOrFail($request->order_id);

        if ($order->payment) {
            return response()->json(['message' => 'This order already has a payment'], 422);
        }

        $payment = Payment::create([
            'user_id'  => $request->user_id,
            'order_id' => $request->order_id,
            'driver_id'=> $request->driver_id,
            'method'   => $request->method,
            'amount'   => $order->total_price,
        ]);

        $order->update(['payment_status' => 'paid']);

        return response()->json([
            'message' => 'Payment processed successfully',
            'payment' => $payment->load(['user', 'order', 'driver.user']),
        ], 201);
    }

    
    public function show($id): JsonResponse
    {
        $payment = Payment::with(['user', 'order.products', 'driver.user'])->findOrFail($id);

        return response()->json($payment);
    }

   
    public function orderPayment($orderId): JsonResponse
    {
        $payment = Payment::with(['user', 'driver.user'])
                          ->where('order_id', $orderId)
                          ->first();

        if (!$payment) {
            return response()->json(['message' => 'No payment found for this order'], 404);
        }

        return response()->json($payment);
    }

    /**
     * مدفوعات مستخدم محدد
     */
    public function userPayments($userId): JsonResponse
    {
        $payments = Payment::with(['order', 'driver.user'])
                           ->where('user_id', $userId)
                           ->latest()
                           ->get();

        return response()->json($payments);
    }
}
