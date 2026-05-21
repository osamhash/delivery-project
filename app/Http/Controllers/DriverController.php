<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\DriverResource;
use App\Models\Driver;
use App\Models\User;
use App\Models\Notification;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class DriverController extends Controller
{
     private function getDriverRating($driverId)
    {
        $reviews = Review::whereHas('order', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })->avg('rating');

        return round($reviews ?: 0, 1);
    }

    private function getTotalReviews($driverId)
    {
        return Review::whereHas('order', function($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })->count();
    }

    private function getCompletedOrdersCount($driverId)
    {
        $completedStatusId = OrderStatus::where('name', 'completed')->value('id');
        return Order::where('driver_id', $driverId)
            ->where('status_id', $completedStatusId)
            ->count();
    }

    public function dashboard()
    {
        $driver = Auth::user()->driver;

        $pendingStatusId = OrderStatus::where('name', 'pending')->value('id');
        $acceptedStatusId = OrderStatus::where('name', 'accepted')->value('id');
        $onTheWayStatusId = OrderStatus::where('name', 'on_the_way')->value('id');
        $completedStatusId = OrderStatus::where('name', 'completed')->value('id');

        $todayOrders = Order::where('driver_id', $driver->id)
            ->whereDate('created_at', today())
            ->count();

        $totalDelivered = $this->getCompletedOrdersCount($driver->id);

        $pendingOrders = Order::where('driver_id', $driver->id)
            ->where('status_id', $pendingStatusId)
            ->with(['user', 'provider.user', 'products'])
            ->orderBy('created_at', 'asc')
            ->get();

        $inProgressOrders = Order::where('driver_id', $driver->id)
            ->whereIn('status_id', [$acceptedStatusId, $onTheWayStatusId])
            ->with(['user', 'provider.user', 'products', 'status'])
            ->orderBy('created_at', 'asc')
            ->get();

        $recentDeliveries = Order::where('driver_id', $driver->id)
            ->where('status_id', $completedStatusId)
            ->with(['user', 'provider'])
            ->orderBy('updated_at', 'desc')
            ->limit(5)
            ->get();

        $rating = $this->getDriverRating($driver->id);
        $totalReviews = $this->getTotalReviews($driver->id);

        return response()->json([
            'driver' => $driver->load('user'),
            'stats' => [
                'today_orders' => $todayOrders,
                'total_delivered' => $totalDelivered,
                'rating' => $rating,
                'total_reviews' => $totalReviews
            ],
            'pending_orders' => $pendingOrders,
            'in_progress_orders' => $inProgressOrders,
            'recent_deliveries' => $recentDeliveries
        ]);
    }
    public function updateAvailability(Request $request)
    {
        $request->validate([
            'is_available' => 'required|boolean'
        ]);

        $driver = Auth::user()->driver;
        $driver->is_available = $request->is_available;
        $driver->save();

        return response()->json(['message' => 'تم تحديث الحالة', 'is_available' => $driver->is_available]);
    }

    public function acceptOrder(Order $order)
    {
        $driver = Auth::user()->driver;
        $pendingStatusId = OrderStatus::where('name', 'pending')->value('id');
        $acceptedStatusId = OrderStatus::where('name', 'accepted')->value('id');

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        if ($order->status_id !== $pendingStatusId) {
            return response()->json(['message' => 'لا يمكن قبول هذا الطلب'], 400);
        }

        DB::transaction(function () use ($order, $acceptedStatusId, $driver) {
            $order->update(['status_id' => $acceptedStatusId]);

            $driver->is_available = false;
            $driver->save();

            Notification::create([
                'user_id' => $order->user_id,
                'title' => '✅ تم قبول طلبك',
                'message' => 'تم قبول طلبك من قبل السائق وسيتم توصيله قريباً',
                'type' => 'order_accepted',
                'data' => ['order_id' => $order->id]
            ]);
        });

        return response()->json(['message' => 'تم قبول الطلب بنجاح']);
    }
    public function rejectOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500'
        ]);

        $driver = Auth::user()->driver;
        $pendingStatusId = OrderStatus::where('name', 'pending')->value('id');
        $rejectedStatusId = OrderStatus::where('name', 'rejected')->value('id');

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        DB::transaction(function () use ($order, $request, $driver, $rejectedStatusId) {
            $order->update([
                'status_id' => $rejectedStatusId,
                'rejection_reason' => $request->reason
            ]);

            $driver->is_available = true;
            $driver->save();

            Notification::create([
                'user_id' => $order->user_id,
                'title' => '❌ تم رفض طلبك',
                'message' => "عذراً، تم رفض طلبك. السبب: {$request->reason}",
                'type' => 'order_rejected',
                'data' => ['order_id' => $order->id, 'reason' => $request->reason]
            ]);
        });

        return response()->json(['message' => 'تم رفض الطلب']);
    }
    public function startDelivery(Order $order)
    {
        $driver = Auth::user()->driver;
        $acceptedStatusId = OrderStatus::where('name', 'accepted')->value('id');
        $onTheWayStatusId = OrderStatus::where('name', 'on_the_way')->value('id');

        if ($order->driver_id !== $driver->id || $order->status_id !== $acceptedStatusId) {
            return response()->json(['message' => 'لا يمكن بدء التوصيل'], 400);
        }

        $order->update(['status_id' => $onTheWayStatusId]);

        Notification::create([
            'user_id' => $order->user_id,
            'title' => '🚚 طلبك في الطريق',
            'message' => 'السائق في طريقه لتوصيل طلبك',
            'type' => 'order_on_the_way',
            'data' => ['order_id' => $order->id]
        ]);

        return response()->json(['message' => 'تم بدء التوصيل']);
    }
    public function completeDelivery(Order $order)
    {
        $driver = Auth::user()->driver;
        $onTheWayStatusId = OrderStatus::where('name', 'on_the_way')->value('id');
        $completedStatusId = OrderStatus::where('name', 'completed')->value('id');

        if ($order->driver_id !== $driver->id || $order->status_id !== $onTheWayStatusId) {
            return response()->json(['message' => 'لا يمكن إكمال التوصيل'], 400);
        }

        DB::transaction(function () use ($order, $completedStatusId, $driver) {
            $order->update(['status_id' => $completedStatusId]);

            $driver->is_available = true;
            $driver->save();

            Notification::create([
                'user_id' => $order->user_id,
                'title' => '📦 تم توصيل طلبك',
                'message' => 'تم توصيل طلبك بنجاح. يرجى دفع المبلغ وتقييم الخدمة',
                'type' => 'order_completed',
                'data' => ['order_id' => $order->id]
            ]);
        });

        return response()->json(['message' => 'تم إكمال التوصيل']);
    }

    public function getOrderDetails(Order $order)
    {
        $driver = Auth::user()->driver;

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return response()->json($order->load(['user', 'provider.user', 'products', 'review', 'status']));
    }

    public function getProfile()
    {
        $user = Auth::user()->load('driver');
        $driverId = $user->driver->id;

        // Add computed values
        $user->driver->rating = $this->getDriverRating($driverId);
        $user->driver->total_reviews = $this->getTotalReviews($driverId);

        return response()->json($user);
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . Auth::id(),
            'address' => 'nullable|string|max:100',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
        ]);

        $user = Auth::user();

        if ($request->has('first_name')) $user->first_name = $request->first_name;
        if ($request->has('last_name')) $user->last_name = $request->last_name;
        if ($request->has('phone')) $user->phone = $request->phone;
        if ($request->has('address')) $user->address = $request->address;

        if ($request->hasFile('image')) {
            if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                Storage::disk('public')->delete($user->image_path);
            }

            $path = $request->file('image')->store('drivers', 'public');
            $user->image_path = $path;
        }

        $user->save();

        return response()->json([
            'message' => 'تم تحديث الملف الشخصي',
            'user' => $user->load('driver')
        ]);
    }

    public function getDeliveryHistory()
    {
        $driver = Auth::user()->driver;
        $completedStatusId = OrderStatus::where('name', 'completed')->value('id');
        $rejectedStatusId = OrderStatus::where('name', 'rejected')->value('id');

        $orders = Order::where('driver_id', $driver->id)
            ->whereIn('status_id', [$completedStatusId, $rejectedStatusId])
            ->with(['user', 'provider', 'review', 'status'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

     // GET /api/v1/drivers
    //    جميع السائقين

    public static function index(): AnonymousResourceCollection
    {
        $drivers = Driver::with('user')->get();
        return DriverResource::collection($drivers);
    }


     //GET /api/v1/drivers/available
    // السائقون المتاحون فقط

    public function available(): AnonymousResourceCollection
    {
        $drivers = Driver::with('user')
            ->where('is_available', true)
            ->get();

        return DriverResource::collection($drivers);
    }




    public function availableDrivers()
    {
        return Driver::where('is_available', true)
            ->with('user') // عشان الاسم
            ->get();
    }
}
