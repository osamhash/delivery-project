<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\CustomerRequest;
use App\Models\Order;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{

   public function dashboard(Request $request)
    {
        $user = $request->user();

        // حماية من أي relation break
        $activeOrdersCount = Order::where('user_id', $user->id)
            ->whereIn('status_id', [1, 2])
            ->count();

        $favoritesCount = $user->favoriteProviders()
            ? $user->favoriteProviders()->count()
            : 0;

       $orders = Order::with(['provider', 'driver', 'status', 'review'])
        ->where('user_id', $user->id)
        ->latest()
        ->take(10)
        ->get();
        $rich = 8;
        $monthlySpending = Order::where('user_id', $user->id)
        ->whereMonth('created_at', now()->month)
        ->sum('total_price');

        $lastMonthSpending = Order::where('user_id', $user->id)
        ->whereMonth('created_at', now()->subMonth()->month)
        ->sum('total_price');

        return response()->json([
            'active_orders' => $activeOrdersCount,
            'favorites' => $favoritesCount,
            'orders' => $orders,
            'monthly_spending' => $monthlySpending,
            'last_month_spending' => $lastMonthSpending,
        ]);
    }


    //get alll customers
    public static function index()
    {
        $role = Role::where('name', 'customer')->first();

        if (!$role) {
            return response()->json([]);
        }

        $customers = User::where('role_id', $role->id)->get();

        return response()->json([
            'data' => $customers
        ]);
    }

    // post customer
    public function store(Request $request)
    {
        $data = $request->validate([
            'first_name'  => 'required|string',
            'second_name' => 'nullable|string',
            'last_name'   => 'required|string',
            'email'       => 'required|email|unique:users',
            'password'    => 'required|min:6',
            'phone'       => 'nullable|string',
        ]);

        $role = Role::where('name', 'customer')->first();

        $customer = User::create([
            'first_name'  => $data['first_name'],
            'second_name' => $data['second_name'] ?? null,
            'last_name'   => $data['last_name'],
            'email'       => $data['email'],
            'password'    => Hash::make($data['password']),
            'phone'       => $data['phone'] ?? null,
            'role_id'     => $role->id,
        ]);

        return response()->json([
            'message' => 'Customer created successfully',
            'data' => $customer
        ], 201);
    }

    //جلب زبون محدد
    public function show(User $user): JsonResponse
    {
        return response()->json([
            'id'         => $user->id,
            'first_name' => $user->first_name,
            'last_name'  => $user->last_name,
            'email'      => $user->email,
            'phone'      => $user->phone,
            'address'    => $user->address,
            'image_path' => $user->image_path,
        ]);
    }


    //update customer
    public function update(CustomerRequest $request, $id)
    {
        $customer = User::find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found'
            ], 404);
        }

        $customer->update([
            'first_name'  => $request->first_name,
            'second_name' => $request->second_name,
            'last_name'   => $request->last_name,
            'email'       => $request->email,
            'phone'       => $request->phone,
            'address'     => $request->address,
        ]);

        return response()->json([
            'message' => 'Customer updated successfully',
            'data' => $customer
        ]);
    }

    //delete customer
    public function destroy($id)
    {
        $customer = User::find($id);

        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found'
            ], 404);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully'
        ]);
    }
}
