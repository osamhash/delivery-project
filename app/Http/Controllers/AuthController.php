<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Provider;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    //Register New Customer
    public function register(Request $request): JsonResponse
    {
        $request->validate([
            'first_name'            => 'required|string|max:100',
            'last_name'             => 'required|string|max:100',
            'email'                 => 'required|email|unique:users,email',
            'password'              => 'required|min:6|confirmed',
            'phone'                 => 'nullable|string|max:10|unique:users,phone',
            'address'               => 'nullable|string|max:100',
            'gender'                => 'nullable|in:0,1',
            'role'                  => 'required|in:customer,provider,driver',
            'provider_type'         => 'required_if:role,provider|nullable|string|max:100',
            'image'                 => 'nullable|image|mimes:jpeg,png,webp|max:2048',
        ], [
            'email.unique'          => 'هذا البريد الإلكتروني مستخدم مسبقاً',
            'phone.unique'          => 'رقم الهاتف مستخدم مسبقاً',
            'image.max'             => 'حجم الصورة يجب أن يكون أقل من 2 ميجابايت',
            'provider_type.required_if' => 'نوع المتجر مطلوب عند التسجيل كتاجر',
        ]);

        // ── رفع الصورة إذا وُجدت
        $imagePath = null;
        if ($request->hasFile('image')) {
            // يحفظ في storage/app/public/users/
            $imagePath = $request->file('image')->store('users', 'public');
        }

        // ── إنشاء المستخدم
        $role = Role::where('name', $request->role)->firstOrFail();

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'phone'      => $request->phone,
            'address'    => $request->address,
            'gender'     => $request->gender,  // 1=ذكر, 0=أنثى, null=غير محدد
            'role_id'    => $role->id,
            'image_path' => $imagePath,
        ]);

        // ── إنشاء السجل المرتبط حسب الدور
        if ($request->role === 'provider') {
            Provider::create([
                'user_id' => $user->id,
                'type'    => $request->provider_type,
            ]);
        }

        if ($request->role === 'driver') {
            Driver::create([
                'user_id'      => $user->id,
                'is_available' => true,
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'تم إنشاء الحساب بنجاح',
            'token'   => $token,
            'user'    => [
                'id'         => $user->id,
                'first_name' => $user->first_name,
                'last_name'  => $user->last_name,
                'email'      => $user->email,
                'phone'      => $user->phone,
                'address'    => $user->address,
                'gender'     => $user->gender,
                'image_path' => $user->image_path
                    ? asset('storage/' . $user->image_path)
                    : null,
                'role'       => $role->name,
            ],
        ], 201);
    }

    //login
    public function login(Request $request)
    {
        $request->validate([
            'email'  => 'required|email',
            'password' => 'required|min:6',
        ]);

        $user = User::where('email', $request->email)
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Email not found'
            ], 404);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Wrong password'
            ], 401);
        }
        // حذف أي توكنات قديمة
        $user->tokens()->delete();

        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'user' => [
            'id' => $user->id,
            'name' => $user->first_name . ' ' . $user->last_name,
            'email' => $user->email,
            'role' => $user->role->name
            ]
        ]);
    }

    //
    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'تم تسجيل الخروج']);
    }

    //my
    // public function me(Request $request)
    // {
    //     return response()->json([

    //         'user' => $request->user()->load('role')
    //     ]);
    // }
    public function me(Request $request): JsonResponse
{
    $user = $request->user()->fresh()->load('role');

    return response()->json([
        'user' => [
            'id'            => $user->id,
            'first_name'    => $user->first_name,
            'second_name'   => $user->second_name,
            'last_name'     => $user->last_name,
            'email'         => $user->email,
            'phone'         => $user->phone,
            'address'       => $user->address,
            'gender'        => $user->gender,
            'date_of_birth' => $user->date_of_birth,
            'image_path'    => $user->image_path
                ? asset('storage/' . str_replace('\\', '/', $user->image_path))
                : null,
            'role'          => $user->role->name,
            'provider'      => $user->provider,
        ]
    ]);
}


    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.exists' => 'لا يوجد حساب مرتبط بهذا البريد الإلكتروني',
        ]);

        // Laravel يرسل الإيميل تلقائياً
        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json([
                'message' => 'تم إرسال رابط إعادة التعيين إلى بريدك الإلكتروني ✉️',
            ]);
        }

        return response()->json([
            'message' => 'حدث خطأ، يرجى المحاولة لاحقاً',
        ], 500);
    }

    // ════════════════════════════════════════════════
    //  RESET PASSWORD
    // ════════════════════════════════════════════════
    public function resetPassword(Request $request): JsonResponse
    {
        $request->validate([
            'token'                 => 'required',
            'email'                 => 'required|email',
            'password'              => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                $user->tokens()->delete(); // احذف كل التوكنات القديمة
                event(new PasswordReset($user));
            }
        );

        return match($status) {
            Password::PASSWORD_RESET => response()->json(['message' => 'تم تغيير كلمة المرور بنجاح ✅']),
            Password::INVALID_TOKEN  => response()->json(['message' => 'الرابط منتهي الصلاحية، اطلب رابطاً جديداً'], 422),
            Password::INVALID_USER   => response()->json(['message' => 'البريد الإلكتروني غير مسجّل'], 422),
            default                  => response()->json(['message' => 'حدث خطأ غير متوقع'], 500),
        };
    }
}



