<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //Register New Customer
    public function register(Request $request)
    {
        $request->validate([
            'first_name' => 'required|string',
            'last_name'  => 'required|string',
            'email'      => 'required|email|unique:users',
            'password'   => 'required|min:6',
            'role'       => 'required' // customer, driver, provider, admin
        ]);

        $role = Role::where('name', $request->role)->first();

        if (!$role) {
            return response()->json([
                'message' => 'Invalid role'
            ], 400);
        }

        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'email'      => $request->email,
            'password'   => Hash::make($request->password),
            'role_id'    => $role->id,
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'User registered successfully',
            'token' => $token,
            'user' => $user->load('role')
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

        // if ($user->role->name !== $request->role) {
        //     return response()->json([
        //         'message' => 'Role mismatch'
        //     ], 403);
        // }

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
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully'
        ]);
    }

    //my
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('role')
        ]);
    }
}
