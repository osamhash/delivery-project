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
use Illuminate\Support\Facades\Storage;

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


    //update customer 6/2/2026
    // public function update(CustomerRequest $request, $id)
    // {
    //     $customer = User::find($id);

    //     if (!$customer) {
    //         return response()->json([
    //             'message' => 'Customer not found'
    //         ], 404);
    //     }

    //     $customer->update([
    //         'first_name'  => $request->first_name,
    //         'second_name' => $request->second_name,
    //         'last_name'   => $request->last_name,
    //         'email'       => $request->email,
    //         'phone'       => $request->phone,
    //         'address'     => $request->address,
    //     ]);

    //     return response()->json([
    //         'message' => 'Customer updated successfully',
    //         'data' => $customer
    //     ]);
    // }

    public function update(Request $request, $id)
    {
        // التحقق من صحة البيانات
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            // 'email' => 'required|email|unique:users,email,' . $id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:100',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:0,1',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:20048',
        ]);

        // جلب المستخدم
        $user = User::findOrFail($id);

        // التحقق من أن المستخدم الحالي يعدل بياناته الخاصة
        if ($user->id !== $request->user()->id) {
            return response()->json(['message' => 'غير مصرح لك بتعديل هذا الحساب'], 403);
        }

        // تحديث بيانات المستخدم
        $user->update([
            'first_name' => $request->first_name,
            'second_name' => $request->second_name,
            'last_name' => $request->last_name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'date_of_birth' => $request->date_of_birth,
            'gender' => $request->gender,
        ]);

        // معالجة رفع الصورة
        if ($request->hasFile('image')) {
            // حذف الصورة القديمة إذا وجدت
            if ($user->image_path) {
                Storage::disk('public')->delete($user->image_path);
            }

            $imagePath = $request->file('image')->store('users', 'public');
            $user->image_path = $imagePath;
            $user->save();
        }

        return response()->json([
            'message' => 'تم تحديث بيانات الحساب بنجاح',
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'second_name' => $user->second_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'phone' => $user->phone,
                'address' => $user->address,
                'date_of_birth' => $user->date_of_birth,
                'gender' => $user->gender,
                'image_path' => $user->image_path ? asset('storage/' . $user->image_path) : null,
            ]
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
