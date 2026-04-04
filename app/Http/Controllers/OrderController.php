<?php

namespace App\Http\Controllers;

use App\Http\Requests\OrderRequest;
use App\Models\Driver;
use App\Models\Order;
use App\Models\OrderStatus;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    
    public function index(): JsonResponse
    {
        $orders = Order::with(['user', 'provider.user', 'driver.user', 'status', 'products'])
                       ->latest()
                       ->get();

        return response()->json($orders);
    }

    
    public function store(OrderRequest $request): JsonResponse
    {
        $driver = Driver::where('is_available', true)->findOrFail($request->driver_id);

        $pendingStatus = OrderStatus::where('name', 'pending')->first();

        $totalPrice = 0;
        $products   = [];

        foreach ($request->products as $item) {
            $product = Product::findOrFail($item['product_id']);

            if ($product->quantity < $item['quantity']) {
                return response()->json([
                    'message' => "Insufficient stock for product: {$product->type}",
                ], 422);
            }

            $linePrice    = $product->price * $item['quantity'];
            $totalPrice  += $linePrice;
            $products[$item['product_id']] = [
                'quantity' => $item['quantity'],
                'price'    => $product->price,
            ];

            $product->decrement('quantity', $item['quantity']);
        }

        $order = Order::create([
            'user_id'        => $request->user_id,
            'provider_id'    => $request->provider_id,
            'driver_id'      => $driver->id,
            'status_id'      => $pendingStatus ? $pendingStatus->id : $request->status_id,
            'total_price'    => $totalPrice,
            'payment_status' => 'pending',
            'order_address'  => $request->order_address,
        ]);

        $order->products()->attach($products);

        $driver->update(['is_available' => false]);

        return response()->json([
            'message' => 'Order created successfully',
            'order'   => $order->load(['user', 'provider.user', 'driver.user', 'status', 'products']),
        ], 201);
    }

    
    public function show($id): JsonResponse
    {
        $order = Order::with([
            'user', 'provider.user', 'driver.user',
            'status', 'products', 'payment', 'review.user',
        ])->findOrFail($id);

        return response()->json($order);
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

    public function userOrders($userId): JsonResponse
    {
        $orders = Order::with(['provider.user', 'driver.user', 'status', 'products'])
                       ->where('user_id', $userId)
                       ->latest()
                       ->get();

        return response()->json($orders);
    }

    
    public function providerOrders($providerId): JsonResponse
    {
        $orders = Order::with(['user', 'driver.user', 'status', 'products'])
                       ->where('provider_id', $providerId)
                       ->latest()
                       ->get();

        return response()->json($orders);
    }

    
    public function statuses(): JsonResponse
    {
        return response()->json(OrderStatus::all());
    }
}
