<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Driver;
use App\Models\Order;
use App\Models\Provider;
use App\Models\OrderStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    // GET /customers
    public function getCustomers()
    {
        try {
            $customers = User::where('role_id', 1)
                ->whereNull('deleted_at')
                ->select('id','first_name','second_name','last_name','email','phone','address','image_path','created_at')
                ->orderByDesc('created_at')
                ->get();

            return response()->json([
                'status' => true,
                'data'   => $customers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching customers: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب بيانات الزبائن'
            ], 500);
        }
    }

    // GET /providers
    public function getProviders()
    {
        try {
            $providers = Provider::with([
                'user:id,first_name,second_name,last_name,email,phone,address,image_path'
            ])->get();

            return response()->json([
                'status' => true,
                'data'   => $providers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching providers: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب بيانات المتاجر'
            ], 500);
        }
    }

    // GET /v1/drivers
    public function getDrivers()
    {
        try {
            $drivers = Driver::with([
                'user:id,first_name,second_name,last_name,email,phone,address,image_path'
            ])->get();

            $drivers->each(function ($driver) {
                if (!$driver->user) {
                    $driver->user = (object) [
                        'id' => null,
                        'first_name' => 'سائق',
                        'second_name' => '',
                        'last_name' => '#' . ($driver->id ?? 'غير معروف'),
                        'email' => 'غير مسجل',
                        'phone' => 'غير مسجل',
                        'address' => 'غير محدد',
                        'image_path' => null
                    ];
                }
            });

            Log::info('Drivers fetched successfully:', ['count' => $drivers->count()]);

            return response()->json([
                'status' => true,
                'data'   => $drivers
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching drivers: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب بيانات السائقين',
                'data' => []
            ], 500);
        }
    }

    // GET /orders
    public function getOrders()
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error fetching orders: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في جلب بيانات الطلبات'
            ], 500);
        }
    }

    // PUT /orders/{id}
    public function updateOrder(Request $request, $id)
    {
        try {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], 404);
            }

            // منع تغيير حالة الطلب المكتمل
            if ($order->status_id == 4) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يمكن تغيير حالة طلب مكتمل'
                ], 400);
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
        } catch (\Exception $e) {
            Log::error('Error updating order: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث حالة الطلب'
            ], 500);
        }
    }

    // DELETE /orders/{id}
    public function deleteOrder($id)
    {
        try {
            $order = Order::find($id);
            if (!$order) {
                return response()->json(['status' => false, 'message' => 'الطلب غير موجود'], 404);
            }

            // منع حذف الطلب المكتمل
            if ($order->status_id == 4) {
                return response()->json([
                    'status' => false,
                    'message' => 'لا يمكن إلغاء طلب مكتمل'
                ], 400);
            }

            $order->delete();

            return response()->json([
                'status'  => true,
                'message' => "تم إلغاء الطلب #{$id} بنجاح"
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting order: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في إلغاء الطلب'
            ], 500);
        }
    }

    // PUT /users/{id}
    public function updateUser(Request $request, $id)
    {
        try {
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
        } catch (\Exception $e) {
            Log::error('Error updating user: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث بيانات المستخدم'
            ], 500);
        }
    }

    // DELETE /users/{id}
    public function deleteUser($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['status' => false, 'message' => 'المستخدم غير موجود'], 404);
            }

            if ($user->id === auth()->id()) {
                return response()->json([
                    'status'  => false,
                    'message' => 'لا يمكنك حذف حسابك الخاص'
                ], 403);
            }

            $user->delete();

            return response()->json([
                'status'  => true,
                'message' => "تم حذف المستخدم #{$id} بنجاح"
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في حذف المستخدم'
            ], 500);
        }
    }

    // Helper: خريطة اسم الحالة → ID
    public static function mapStatusNameToId(string $name): ?int
    {
        $map = [
            'Pending'    => 1,
            'Accepted'   => 2,
            'OnTheWay' => 3,
            'Completed'  => 4,
            'Rejected'   => 5,
        ];
        return $map[$name] ?? null;
    }

    // تحديث صورة الادمن - تخزين المسار بصيغة admins/اسم_الصورة
    public function updateAdminProfile(Request $request)
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'status' => false,
                    'message' => 'غير مصرح'
                ], 401);
            }

            $validator = Validator::make($request->all(), [
                'first_name' => 'sometimes|string|max:100',
                'second_name' => 'nullable|string|max:100',
                'last_name' => 'sometimes|string|max:100',
                'email' => 'sometimes|email|max:100|unique:users,email,' . $user->id,
                'phone' => 'nullable|string|max:20|unique:users,phone,' . $user->id,
                'address' => 'nullable|string|max:255',
                'date_of_birth' => 'nullable|date',
                'gender' => 'nullable|boolean',
                'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'errors' => $validator->errors()
                ], 422);
            }

            // تحديث البيانات الأساسية
            if ($request->has('first_name')) $user->first_name = $request->first_name;
            if ($request->has('second_name')) $user->second_name = $request->second_name;
            if ($request->has('last_name')) $user->last_name = $request->last_name;
            if ($request->has('email')) $user->email = $request->email;
            if ($request->has('phone')) $user->phone = $request->phone;
            if ($request->has('address')) $user->address = $request->address;
            if ($request->has('date_of_birth')) $user->date_of_birth = $request->date_of_birth;
            if ($request->has('gender')) $user->gender = $request->gender;

            // معالجة الصورة - تخزينها في مجلد admins
            if ($request->hasFile('image')) {
                // حذف الصورة القديمة
                if ($user->image_path && Storage::disk('public')->exists($user->image_path)) {
                    Storage::disk('public')->delete($user->image_path);
                }

                $image = $request->file('image');
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();

                // تخزين في storage/app/public/admins
                $path = $image->storeAs('admins', $imageName, 'public');

                if (!$path) {
                    return response()->json([
                        'status' => false,
                        'message' => 'فشل في رفع الصورة'
                    ], 500);
                }

                //  حفظ المسار في قاعدة البيانات بصيغة admins/اسم_الصورة
                $user->image_path = $path; // هذا سيكون مثلاً: admins/123456_abc.jpg
            }

            $user->save();

            // تحضير البيانات للرد
            $userData = $user->toArray();
            if ($user->image_path) {
                //  إضافة الرابط الكامل للصورة للعرض
                $userData['image_url'] = Storage::disk('public')->url($user->image_path);
                $userData['image_path'] = $user->image_path;
            }

            Log::info('Admin profile updated successfully', ['user_id' => $user->id]);

            return response()->json([
                'status' => true,
                'message' => 'تم تحديث بيانات المسؤول بنجاح',
                'user' => $userData
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating admin profile: ' . $e->getMessage());
            return response()->json([
                'status' => false,
                'message' => 'فشل في تحديث بيانات المسؤول: ' . $e->getMessage()
            ], 500);
        }
    }
}
