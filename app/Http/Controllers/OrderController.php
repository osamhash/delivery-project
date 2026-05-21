<?php

// namespace App\Http\Controllers;
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Payment;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Type\Integer;

class OrderController extends Controller
{
    //to Admin
    public static function index(): JsonResponse
    {
        $orders = Order::with(['user', 'provider.user', 'driver.user', 'status', 'products'])
                       ->latest()
                       ->get();

        return response()->json($orders);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status_id'      => 'sometimes|exists:order_status,id',
            'payment_status' => 'sometimes|in:pending,paid,failed',
            'driver_id'      => 'sometimes|exists:drivers,id',
        ]);

        $order = Order::findOrFail($id);
        $order->update($request->only(['status_id', 'payment_status', 'driver_id']));

        $completedStatus = OrderStatus::where('name', 'completed')->first();
        if ($completedStatus && $order->status_id == $completedStatus->id) {
            optional($order->driver)->update(['is_available' => true]);
        }

        return response()->json([
            'message' => 'Order updated successfully',
            'order'   => $order->load(['user', 'provider.user', 'driver.user', 'status', 'products']),
        ]);
    }


    public function destroy($id): JsonResponse
    {
        $order = Order::findOrFail($id);

        foreach ($order->products as $product) {
            $product->increment('quantity', $product->pivot->quantity);
        }

        optional($order->driver)->update(['is_available' => true]);

        $order->products()->detach();
        $order->delete();

        return response()->json(['message' => 'Order deleted successfully']);
    }

    // public function userOrders($userId): JsonResponse
    // {
    //     $orders = Order::with(['provider.user', 'driver.user', 'status', 'products'])
    //                    ->where('user_id', $userId)
    //                    ->latest()
    //                    ->get();

    //     return response()->json($orders);
    // }
    public function userOrders($id)
    {
        return Order::where('user_id', $id)
            ->with('products')
            ->latest()
            ->get();
    }

    // public function providerOrders($providerId): JsonResponse
    // {
    //     $orders = Order::with(['user', 'driver.user', 'status', 'products'])
    //                    ->where('provider_id', $providerId)
    //                    ->latest()
    //                    ->get();

    //     return response()->json($orders);
    // }
    public function providerOrders($id)
    {
        return Order::where('provider_id', $id)
            ->with('user')
            ->latest()
            ->get();
    }

    public function statuses(): JsonResponse
    {
        return response()->json(OrderStatus::all());
    }
    //   ___________________________

    //POST /api/v1/orders
    //إنشاء طلب جديد وحفظ منتجاته

    public function store(StoreOrderRequest $request): JsonResponse
    {
        // 1. تأكد أن السائق متاح
        $driver = Driver::findOrFail($request->driver_id);

        if (! $driver->is_available) {
            return response()->json([
                'message' => 'السائق المختار غير متاح حالياً.',
            ], 422);
        }

        DB::beginTransaction();
        try {
            // 2. أنشئ الطلب
            $order = Order::create([
                'user_id'        => $request->user_id,
                'provider_id'    => $request->provider_id,
                'driver_id'      => $request->driver_id,
                'status_id'      => 1,                       // 1 = pending (أول سجل في order_status)
                'total_price'    => $request->total_price,
                'payment_status' => $request->payment_status,
                'order_address'  => $request->order_address,
            ]);

            // 3. أضف المنتجات إلى orders_products
            foreach ($request->products as $item) {
                $order->products()->attach($item['product_id'], [
                    'quantity'   => $item['quantity'],
                    'price'      => $item['price'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            $count = Order::where('user_id', $request->user_id)
            ->where('provider_id', $request->provider_id)
            ->count();

        if ($count >= 10) {
            $user = User::find($request->user_id);

            $user->favoriteProviders()->syncWithoutDetaching([
                $request->provider_id
            ]);
        }
            // 4. اجعل السائق غير متاح
            $driver->update(['is_available' => false]);

            DB::commit();

            return response()->json([
                'message' => 'تم إنشاء الطلب بنجاح.',
                'data'    => new OrderResource($order->load(['products', 'driver.user', 'status'])),
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['message' => 'حدث خطأ: ' . $e->getMessage()], 500);
        }
    }



    public function favorites(Request $request)
    {
        return $request->user()
            ->favoriteProviders()
            ->with('user')
            ->get();
    }

    // GET /api/v1/orders/{order}
    // عرض تفاصيل طلب

   public function show($id)
    {
        $order = Order::with(['products', 'driver.user', 'status', 'user'])
            ->findOrFail($id);

        return response()->json([
            'message' => 'success',
            'data' => new OrderResource($order)
        ]);
    }


     //PATCH /api/v1/orders/{order}/status
    // تغيير حالة الطلب (مثال: من pending → delivered)

    public function updateStatus(Order $order): JsonResponse
    {
        $request = request()->validate([
            'status_id' => 'required|exists:order_status,id',
        ]);

        $order->update(['status_id' => $request['status_id']]);

        // أتح السائق مجدداً إذا أُكمل الطلب أو أُلغي
        $completedStatuses = [3, 4]; // مثال: 3=delivered, 4=cancelled
        if (in_array($request['status_id'], $completedStatuses)) {
            $order->driver->update(['is_available' => true]);
        }

        return response()->json(['message' => 'تم تحديث حالة الطلب.']);
    }

    public function userOrdersPaginated(Request $request)
    {
        return Order::where('user_id', $request->user()->id)
            ->with(['provider', 'status'])
            ->paginate(10);
    }


}
