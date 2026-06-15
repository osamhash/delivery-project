<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Provider;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AdminController extends Controller
{
    // ─────────────────────────────────────────
    // GET /customers
    // ─────────────────────────────────────────
    public function getCustomers()
    {
        // role_id = 3 للزبائن (حسب الـ seed بتاعك)
        $customers = User::where('role_id', 3)
            ->whereNull('deleted_at')
            ->select('id','first_name','second_name','last_name','email','phone','address','image_path','created_at')
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'status' => true,
            'data'   => $customers
        ]);
    }

    // ─────────────────────────────────────────
    // GET /providers
    // ─────────────────────────────────────────
    public function getProviders()
    {
        $providers = Provider::with([
            'user:id,first_name,second_name,last_name,email,phone,address,image_path'
        ])->get();

        return response()->json([
            'status' => true,
            'data'   => $providers
        ]);
    }

    // ─────────────────────────────────────────
    // GET /v1/drivers
    // ─────────────────────────────────────────
    public function getDrivers()
    {
        $drivers = Driver::with([
            'user:id,first_name,second_name,last_name,email,phone,address,image_path'
        ])->get();

        return response()->json([
            'status' => true,
            'data'   => $drivers
        ]);
    }

    // ─────────────────────────────────────────
    // GET /orders
    // ─────────────────────────────────────────
    public function getOrders()
    {
        $orders = Order::with([
            'user:id,first_name,last_name',
            'provider.user:id,first_name,last_name',
            'driver.user:id,first_name,last_name',
            'status:id,name',
        ])
        ->orderByDesc('created_at')
        ->get();

        return response()->json([
            'status' => true,
            'data'   => $orders
        ]);
    }

    // ─────────────────────────────────────────
    // PUT /orders/{id}
    // تغيير حالة الطلب
    // ─────────────────────────────────────────
    public function updateOrder(Request $request, $id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'status_id' => 'required|exists:order_status,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $order->status_id = $request->status_id;
        $order->save();

        $order->load('status:id,name');

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث حالة الطلب',
            'order'   => $order
        ]);
    }

    // ─────────────────────────────────────────
    // DELETE /orders/{id}
    // إلغاء/حذف طلب
    // ─────────────────────────────────────────
    public function deleteOrder($id)
    {
        $order = Order::find($id);
        if (!$order) {
            return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], 404);
        }

        $order->delete();

        return response()->json([
            'status'  => true,
            'message' => "تم إلغاء الطلب #{$id} بنجاح"
        ]);
    }

    // ─────────────────────────────────────────
    // PUT /users/{id}
    // تعديل مستخدم (Modal)
    // ─────────────────────────────────────────
    public function updateUser(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        $validator = Validator::make($request->all(), [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|max:100|unique:users,email,' . $id,
            'phone'      => 'nullable|string|max:20|unique:users,phone,' . $id,
            'address'    => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status'  => false,
                'errors'  => $validator->errors()
            ], 422);
        }

        $user->first_name = $request->first_name;
        $user->last_name  = $request->last_name;
        $user->email      = $request->email;
        $user->phone      = $request->phone;
        $user->address    = $request->address;
        $user->save();

        return response()->json([
            'status'  => true,
            'message' => 'تم تحديث بيانات المستخدم',
            'user'    => $user
        ]);
    }

    // ─────────────────────────────────────────
    // DELETE /users/{id}
    // حذف مستخدم (Soft Delete)
    // ─────────────────────────────────────────
    public function deleteUser($id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'المستخدم غير موجود'], 404);
        }

        // منع حذف الآدمن لنفسه
        if ($user->id === auth()->id()) {
            return response()->json([
                'status'  => false,
                'message' => 'لا يمكنك حذف حسابك الخاص'
            ], 403);
        }

        $user->delete(); // soft delete

        return response()->json([
            'status'  => true,
            'message' => "تم حذف المستخدم #{$id} بنجاح"
        ]);
    }

    // ─────────────────────────────────────────
    // Helper: خريطة اسم الحالة → ID
    // ─────────────────────────────────────────
    public static function mapStatusNameToId(string $name): ?int
    {
        $map = [
            'pending'    => 1,
            'accepted'   => 2,
            'on_the_way' => 3,
            'completed'  => 4,
            'rejected'   => 5,
        ];
        return $map[$name] ?? null;
    }


    public function updateAdminProfile(Request $request)
{
    $user = auth()->user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'غير مصرح'
        ], 401);
    }

    $validator = Validator::make($request->all(), [
        'first_name' => 'required|string|max:100',
        'second_name' => 'nullable|string|max:100',
        'last_name' => 'required|string|max:100',
        'email' => 'required|email|max:100|unique:users,email,' . $user->id,
        'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
        'address' => 'nullable|string|max:255',
        'date_of_birth' => 'nullable|date',
        'gender' => 'nullable|boolean',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // تحديث البيانات
    $user->first_name = $request->first_name;
    $user->second_name = $request->second_name;
    $user->last_name = $request->last_name;
    $user->email = $request->email;
    $user->phone = $request->phone;
    $user->address = $request->address;
    $user->date_of_birth = $request->date_of_birth;
    $user->gender = $request->gender;

    // ✅ رفع الصورة إلى مجلد admins
    if ($request->hasFile('image')) {
        // حذف الصورة القديمة إذا وجدت
        if ($user->image_path && file_exists(storage_path('app/public/' . $user->image_path))) {
            unlink(storage_path('app/public/' . $user->image_path));
        }

        $image = $request->file('image');
        $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

        // تخزين في storage/app/public/admins
        $image->storeAs('public/admins', $imageName);

        // حفظ المسار في قاعدة البيانات
        $user->image_path = 'admins/' . $imageName;
    }

    $user->save();

    return response()->json([
        'status' => true,
        'message' => 'تم تحديث بيانات المسؤول بنجاح',
        'user' => $user
    ]);
}
}
