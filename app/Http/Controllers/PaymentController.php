<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Notification;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    //  Helper Method
    private function canBePaid($order)
    {
        $completedStatusId = OrderStatus::where('name', 'completed')->value('id');
        return $order->status_id === $completedStatusId && $order->payment_method === 'pending';
    }

    public function payOnDelivery(Order $order)
    {
        // Check if user owns this order
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        // Check if order can be paid
        if (!$this->canBePaid($order)) {
            return response()->json(['message' => 'لا يمكن دفع هذا الطلب'], 400);
        }

        DB::transaction(function () use ($order) {
            $order->update(['payment_method' => 'paid']);

            // Notify driver
            if ($order->driver && $order->driver->user_id) {
                Notification::create([
                    'user_id' => $order->driver->user_id,
                    'title' => '💰 تم دفع الطلب',
                    'message' => "تم دفع الطلب #{$order->id} بنجاح",
                    'type' => 'payment_received',
                    'data' => ['order_id' => $order->id]
                ]);
            }
        });

        return response()->json([
            'message' => 'تم الدفع بنجاح',
            'payment_method' => $order->payment_method
        ]);
    }

    public function getPaymentStatus(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return response()->json([
            'payment_method' => $order->payment_method,
            'can_pay' => $this->canBePaid($order)
        ]);
    }
}
