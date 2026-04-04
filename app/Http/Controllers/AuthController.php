<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    
    public function register(RegisterRequest $request): JsonResponse
    {
        $role = Role::where('name', $request->role)->firstOrFail();

        $user = User::create([
            'first_name'   => $request->first_name,
            'second_name'  => $request->second_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'phone'        => $request->phone,
            'role_id'      => $role->id,
            'gender'       => $request->gender,
            'address'      => $request->address,
            'date_of_birth'=> $request->date_of_birth,
        ]);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'User registered successfully',
            'user'    => $user->load('role'),
            'token'   => $token,
        ], 201);
    }

    
    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->only('email', 'password');

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth('api')->user();

        return response()->json([
            'message' => 'Login successful',
            'user'    => $user->load('role'),
            'token'   => $token,
        ]);
    }

    
    public function logout(): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());

        return response()->json(['message' => 'Logged out successfully']);
    }

    
    public function profile(): JsonResponse
    {
        $user = auth('api')->user()->load('role', 'driver', 'provider');

        return response()->json($user);
    }

   
    public function updateProfile(RegisterRequest $request): JsonResponse
    {
        $user = auth('api')->user();

        $data = $request->only([
            'first_name', 'second_name', 'last_name',
            'phone', 'gender', 'address', 'date_of_birth',
        ]);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user'    => $user->fresh()->load('role'),
        ]);
    }

   
    public function refresh(): JsonResponse
    {
        try {
            $newToken = JWTAuth::refresh(JWTAuth::getToken());
        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {
            return response()->json(['message' => 'Token expired, please login again'], 401);
        }

        return response()->json(['token' => $newToken]);
    }
}
