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
    // ─── Helpers ──────────────────────────────────────────────

    private function getDriverRating($driverId)
    {
        $reviews = Review::whereHas('order', function ($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })->avg('rating');

        return round($reviews ?: 0, 1);
    }

    private function getTotalReviews($driverId)
    {
        return Review::whereHas('order', function ($q) use ($driverId) {
            $q->where('driver_id', $driverId);
        })->count();
    }

    private function getCompletedOrdersCount($driverId)
    {
        $completedStatusId = OrderStatus::where('name', 'Completed')->value('id');
        return Order::where('driver_id', $driverId)
            ->where('status_id', $completedStatusId)
            ->count();
    }

    // ─── Dashboard ────────────────────────────────────────────

    public function dashboard()
    {
        $driver = Auth::user()->driver;

        $pendingStatusId   = OrderStatus::where('name', 'Pending')->value('id');
        $acceptedStatusId  = OrderStatus::where('name', 'Accepted')->value('id');
        $onTheWayStatusId  = OrderStatus::where('name', 'OnTheWay')->value('id');
        $completedStatusId = OrderStatus::where('name', 'Completed')->value('id');

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

        $rating       = $this->getDriverRating($driver->id);
        $totalReviews = $this->getTotalReviews($driver->id);

        // كمية الطلبات التي تحتاج دفع كاش (مكتملة ولم تُدفع)
        $pendingCashOrders = Order::where('driver_id', $driver->id)
            ->where('status_id', $completedStatusId)
            ->where('payment_method', 'Pending')
            ->with(['user'])
            ->get();

        return response()->json([
            'driver'              => $driver->load('user'),
            'stats'               => [
                'today_orders'   => $todayOrders,
                'total_delivered' => $totalDelivered,
                'rating'          => $rating,
                'total_reviews'   => $totalReviews,
            ],
            'pending_orders'      => $pendingOrders,
            'in_progress_orders'  => $inProgressOrders,
            'recent_deliveries'   => $recentDeliveries,
            'pending_cash_orders' => $pendingCashOrders,
        ]);
    }

    // ─── Availability ─────────────────────────────────────────

    public function updateAvailability(Request $request)
    {
        $request->validate(['is_available' => 'required|boolean']);

        $driver               = Auth::user()->driver;
        $driver->is_available = $request->is_available;
        $driver->save();

        return response()->json([
            'message'      => 'تم تحديث الحالة',
            'is_available' => $driver->is_available,
        ]);
    }

    // ─── Accept Order ─────────────────────────────────────────

    public function acceptOrder(Order $order)
    {
        $driver           = Auth::user()->driver;
        $pendingStatusId  = OrderStatus::where('name', 'Pending')->value('id');
        $acceptedStatusId = OrderStatus::where('name', 'Accepted')->value('id');

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
                'title'   => '✅ تم قبول طلبك',
                'message' => 'تم قبول طلبك من قبل السائق وسيتم توصيله قريباً',
                'type'    => 'order_accepted',
                'data'    => ['order_id' => $order->id],
            ]);
        });

        return response()->json(['message' => 'تم قبول الطلب بنجاح']);
    }

    // ─── Reject Order ─────────────────────────────────────────

    public function rejectOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $driver           = Auth::user()->driver;
        $rejectedStatusId = OrderStatus::where('name', 'Rejected')->value('id');

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        DB::transaction(function () use ($order, $request, $driver, $rejectedStatusId) {
            $order->update([
                'status_id'        => $rejectedStatusId,
                'rejection_reason' => $request->reason,
            ]);

            $driver->is_available = true;
            $driver->save();

            Notification::create([
                'user_id' => $order->user_id,
                'title'   => '❌ تم رفض طلبك',
                'message' => "عذراً، تم رفض طلبك. السبب: {$request->reason}",
                'type'    => 'order_rejected',
                'data'    => ['order_id' => $order->id, 'reason' => $request->reason],
            ]);
        });

        return response()->json(['message' => 'تم رفض الطلب']);
    }

    // ─── Cancel Order (while OnTheWay) ──────────────────────
    /**
     * POST /api/v1/driver/orders/{order}/cancel
     * السائق يلغي الطلب أثناء التوصيل
     */
    public function cancelOrder(Request $request, Order $order)
    {
        $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        $driver          = Auth::user()->driver;
        $acceptedStatusId = OrderStatus::where('name', 'Accepted')->value('id');
        $onTheWayStatusId = OrderStatus::where('name', 'OnTheWay')->value('id');
        $cancelledStatusId = OrderStatus::where('name', 'cancelled')->value('id');

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        // يمكن الإلغاء فقط إذا كان الطلب مقبولاً أو في الطريق
        if (!in_array($order->status_id, [$acceptedStatusId, $onTheWayStatusId])) {
            return response()->json(['message' => 'لا يمكن إلغاء هذا الطلب في وضعه الحالي'], 400);
        }

        DB::transaction(function () use ($order, $request, $driver, $cancelledStatusId) {
            $order->update([
                'status_id'        => $cancelledStatusId,
                'rejection_reason' => $request->reason,
            ]);

            $driver->is_available = true;
            $driver->save();

            // إشعار الزبون
            Notification::create([
                'user_id' => $order->user_id,
                'title'   => '🚫 تم إلغاء طلبك',
                'message' => "عذراً، قام السائق بإلغاء طلبك. السبب: {$request->reason}",
                'type'    => 'order_cancelled',
                'data'    => ['order_id' => $order->id, 'reason' => $request->reason],
            ]);

            // إشعار المتجر أيضاً
            if ($order->provider && $order->provider->user_id) {
                Notification::create([
                    'user_id' => $order->provider->user_id,
                    'title'   => '🚫 تم إلغاء طلب',
                    'message' => "تم إلغاء الطلب #{$order->id} من قِبل السائق",
                    'type'    => 'order_cancelled',
                    'data'    => ['order_id' => $order->id],
                ]);
            }
        });

        return response()->json(['message' => 'تم إلغاء الطلب']);
    }

    // ─── Start Delivery ───────────────────────────────────────

    public function startDelivery(Order $order)
    {
        $driver           = Auth::user()->driver;
        $acceptedStatusId = OrderStatus::where('name', 'Accepted')->value('id');
        $onTheWayStatusId = OrderStatus::where('name', 'OnTheWay')->value('id');

        if ($order->driver_id !== $driver->id || $order->status_id !== $acceptedStatusId) {
            return response()->json(['message' => 'لا يمكن بدء التوصيل'], 400);
        }

        $order->update(['status_id' => $onTheWayStatusId]);

        Notification::create([
            'user_id' => $order->user_id,
            'title'   => '🚚 طلبك في الطريق',
            'message' => 'السائق في طريقه لتوصيل طلبك',
            'type'    => 'order_OnTheWay',
            'data'    => ['order_id' => $order->id],
        ]);

        return response()->json(['message' => 'تم بدء التوصيل']);
    }

    // ─── Complete Delivery ────────────────────────────────────

    public function completeDelivery(Order $order)
    {
        $driver           = Auth::user()->driver;
        $onTheWayStatusId = OrderStatus::where('name', 'OnTheWay')->value('id');
        $completedStatusId = OrderStatus::where('name', 'Completed')->value('id');

        if ($order->driver_id !== $driver->id || $order->status_id !== $onTheWayStatusId) {
            return response()->json(['message' => 'لا يمكن إكمال التوصيل'], 400);
        }

        DB::transaction(function () use ($order, $completedStatusId, $driver) {
            $order->update(['status_id' => $completedStatusId]);

            $driver->is_available = true;
            $driver->save();

            Notification::create([
                'user_id' => $order->user_id,
                'title'   => '📦 تم توصيل طلبك',
                'message' => 'تم توصيل طلبك بنجاح. يرجى دفع المبلغ وتقييم الخدمة',
                'type'    => 'order_completed',
                'data'    => ['order_id' => $order->id],
            ]);
        });

        return response()->json(['message' => 'تم إكمال التوصيل']);
    }

    // ─── Confirm Cash Payment ─────────────────────────────────
    /**
     * POST /api/v1/driver/orders/{order}/confirm-cash
     * السائق يؤكد استلام المبلغ كاش
     */
    public function confirmCashPayment(Order $order)
    {
        $driver            = Auth::user()->driver;
        $completedStatusId = OrderStatus::where('name', 'Completed')->value('id');

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        if ($order->status_id !== $completedStatusId) {
            return response()->json(['message' => 'الطلب لم يكتمل بعد'], 400);
        }

        if ($order->payment_method === 'paid') {
            return response()->json(['message' => 'تم الدفع مسبقاً'], 400);
        }

        DB::transaction(function () use ($order) {
            $order->update(['payment_method' => 'paid']);

            // إشعار الزبون بتأكيد الدفع
            Notification::create([
                'user_id' => $order->user_id,
                'title'   => '💰 تم تأكيد الدفع',
                'message' => "تم تأكيد استلام مبلغ الطلب #{$order->id}. شكراً لتعاملك معنا!",
                'type'    => 'payment_confirmed',
                'data'    => ['order_id' => $order->id],
            ]);

            // إشعار المتجر
            if ($order->provider && $order->provider->user_id) {
                Notification::create([
                    'user_id' => $order->provider->user_id,
                    'title'   => '💰 تم استلام الدفع',
                    'message' => "تم استلام مبلغ الطلب #{$order->id} كاش",
                    'type'    => 'payment_received',
                    'data'    => ['order_id' => $order->id],
                ]);
            }
        });

        return response()->json([
            'message'        => 'تم تأكيد استلام المبلغ',
            'payment_method' => 'paid',
        ]);
    }

    // ─── Order Details ────────────────────────────────────────

    public function getOrderDetails(Order $order)
    {
        $driver = Auth::user()->driver;

        if ($order->driver_id !== $driver->id) {
            return response()->json(['message' => 'غير مصرح'], 403);
        }

        return response()->json($order->load(['user', 'provider.user', 'products', 'review', 'status']));
    }

    // ─── Profile ──────────────────────────────────────────────

    public function getProfile()
    {
        $user   = Auth::user();
        $driver = $user->driver;

        if (!$driver) {
            return response()->json(['status' => false, 'message' => 'لم يتم إيجاد سجل السائق'], 404);
        }

        return response()->json([
            'status'        => true,
            'id'            => $user->id,
            'first_name'    => $user->first_name,
            'second_name'   => $user->second_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'address'       => $user->address,
            'date_of_birth' => $user->date_of_birth,
            'gender'        => $user->gender,
            'image_path'    => $user->image_path ? asset('storage/' . $user->image_path) : null,
            'driver'        => [
                'id'            => $driver->id,
                'is_available'  => (bool) $driver->is_available,
                'rating'        => (string) $this->getDriverRating($driver->id),
                'total_reviews' => $this->getTotalReviews($driver->id),
            ],
        ]);
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'first_name'    => 'sometimes|string|max:100',
            'second_name'   => 'nullable|string|max:100',
            'last_name'     => 'sometimes|string|max:100',
            'email'         => 'sometimes|email|max:100|unique:users,email,' . $user->id,
            'phone'         => 'nullable|string|max:20|unique:users,phone,' . $user->id,
            'address'       => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender'        => 'nullable|in:0,1',
            'image'         => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($request->has('first_name'))    $user->first_name    = $request->first_name;
        if ($request->has('second_name'))   $user->second_name   = $request->second_name;
        if ($request->has('last_name'))     $user->last_name     = $request->last_name;
        if ($request->has('email'))         $user->email         = $request->email;
        if ($request->has('phone'))         $user->phone         = $request->phone;
        if ($request->has('address'))       $user->address       = $request->address;
        if ($request->has('date_of_birth')) $user->date_of_birth = $request->date_of_birth;
        if ($request->has('gender'))        $user->gender        = $request->gender;

        if ($request->hasFile('image')) {
            if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                Storage::disk('public')->delete($user->image_path);
            }
            $user->image_path = $request->file('image')->store('avatars', 'public');
        }

        $user->save();
        $driver = $user->driver;

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث الملف الشخصي',
            'user'    => [
                'id'            => $user->id,
                'first_name'    => $user->first_name,
                'second_name'   => $user->second_name,
                'last_name'     => $user->last_name,
                'email'         => $user->email,
                'phone'         => $user->phone,
                'address'       => $user->address,
                'date_of_birth' => $user->date_of_birth,
                'gender'        => $user->gender,
                'image_path'    => $user->image_path ? asset('storage/' . $user->image_path) : null,
                'driver'        => [
                    'id'           => $driver->id,
                    'is_available' => (bool) $driver->is_available,
                ],
            ],
        ]);
    }

    // ─── Delivery History ─────────────────────────────────────

    public function getDeliveryHistory()
    {
        $driver            = Auth::user()->driver;
        $completedStatusId = OrderStatus::where('name', 'Completed')->value('id');
        $rejectedStatusId  = OrderStatus::where('name', 'Rejected')->value('id');
        $cancelledStatusId = OrderStatus::where('name', 'cancelled')->value('id');

        $orders = Order::where('driver_id', $driver->id)
            ->whereIn('status_id', [$completedStatusId, $rejectedStatusId, $cancelledStatusId])
            ->with(['user', 'provider', 'review', 'status'])
            ->orderBy('updated_at', 'desc')
            ->paginate(10);

        return response()->json($orders);
    }

    // ─── All / Available Drivers ──────────────────────────────

    public static function index(): AnonymousResourceCollection
    {
        $drivers = Driver::with('user')->get();
        return DriverResource::collection($drivers);
    }

    public function available(): AnonymousResourceCollection
    {
        $drivers = Driver::with('user')->where('is_available', true)->get();
        return DriverResource::collection($drivers);
    }

    public function availableDrivers()
    {
        return Driver::where('is_available', true)->with('user')->get();
    }
}
