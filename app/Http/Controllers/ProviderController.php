<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProviderRequest;
use App\Models\Provider;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ProviderController extends Controller
{
    /**
     * قائمة جميع مقدمي الخدمة
     */
    public function index(): JsonResponse
    {
        $providers = Provider::with('user.role')->get();

        return response()->json($providers);
    }

    /**
     * إنشاء مقدم خدمة جديد (مع إنشاء حساب مستخدم)
     */
    public function store(ProviderRequest $request): JsonResponse
    {
        $providerRole = Role::where('name', 'provider')->firstOrFail();

        $user = User::create([
            'first_name'   => $request->first_name,
            'second_name'  => $request->second_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'phone'        => $request->phone,
            'role_id'      => $providerRole->id,
            'gender'       => $request->gender,
            'address'      => $request->address,
        ]);

        $provider = Provider::create([
            'user_id' => $user->id,
            'type'    => $request->type,
        ]);

        return response()->json([
            'message'  => 'Provider created successfully',
            'provider' => $provider->load('user.role'),
        ], 201);
    }

    /**
     * عرض مقدم خدمة محدد مع منتجاته
     */
    public function show($id): JsonResponse
    {
        $provider = Provider::with(['user.role', 'products', 'orders.status'])->findOrFail($id);

        return response()->json($provider);
    }

    /**
     * تحديث بيانات مقدم الخدمة
     */
    public function update(Request $request, $id): JsonResponse
    {
        $provider = Provider::findOrFail($id);

        if ($request->has('type')) {
            $provider->update(['type' => $request->type]);
        }

        $userFields = $request->only([
            'first_name', 'second_name', 'last_name',
            'phone', 'address', 'gender',
        ]);

        if (!empty($userFields)) {
            $provider->user->update($userFields);
        }

        return response()->json([
            'message'  => 'Provider updated successfully',
            'provider' => $provider->load('user.role'),
        ]);
    }

    /**
     * حذف مقدم خدمة
     */
    public function destroy($id): JsonResponse
    {
        $provider = Provider::findOrFail($id);
        $user     = $provider->user;

        $provider->delete();
        $user->delete();

        return response()->json(['message' => 'Provider deleted successfully']);
    }

    /**
     * منتجات مقدم خدمة معين
     */
    public function products($id): JsonResponse
    {
        $provider = Provider::findOrFail($id);
        $products = $provider->products()->get();

        return response()->json($products);
    }
}
