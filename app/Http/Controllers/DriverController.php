<?php

namespace App\Http\Controllers;

use App\Http\Requests\DriverRequest;
use App\Models\Driver;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    
    public function index(): JsonResponse
    {
        $drivers = Driver::with('user.role')->get();

        return response()->json($drivers);
    }

    
    public function store(DriverRequest $request): JsonResponse
    {
        $driverRole = Role::where('name', 'driver')->firstOrFail();

        $user = User::create([
            'first_name'   => $request->first_name,
            'second_name'  => $request->second_name,
            'last_name'    => $request->last_name,
            'email'        => $request->email,
            'password'     => Hash::make($request->password),
            'phone'        => $request->phone,
            'role_id'      => $driverRole->id,
            'gender'       => $request->gender,
            'address'      => $request->address,
        ]);

        $driver = Driver::create([
            'user_id'      => $user->id,
            'is_available' => $request->is_available ?? true,
        ]);

        return response()->json([
            'message' => 'Driver created successfully',
            'driver'  => $driver->load('user.role'),
        ], 201);
    }

    public function show($id): JsonResponse
    {
        $driver = Driver::with(['user.role', 'orders.status'])->findOrFail($id);

        return response()->json($driver);
    }

   
    public function update(Request $request, $id): JsonResponse
    {
        $driver = Driver::findOrFail($id);

        if ($request->has('is_available')) {
            $driver->update(['is_available' => $request->is_available]);
        }

        $userFields = $request->only([
            'first_name', 'second_name', 'last_name',
            'phone', 'address', 'gender',
        ]);

        if (!empty($userFields)) {
            $driver->user->update($userFields);
        }

        return response()->json([
            'message' => 'Driver updated successfully',
            'driver'  => $driver->load('user.role'),
        ]);
    }

    public function destroy($id): JsonResponse
    {
        $driver = Driver::findOrFail($id);
        $user   = $driver->user;

        $driver->delete();
        $user->delete();

        return response()->json(['message' => 'Driver deleted successfully']);
    }

    
    public function available(): JsonResponse
    {
        $drivers = Driver::with('user')->where('is_available', true)->get();

        return response()->json($drivers);
    }

   
    public function toggleAvailability($id): JsonResponse
    {
        $driver = Driver::findOrFail($id);
        $driver->update(['is_available' => !$driver->is_available]);

        return response()->json([
            'message'      => 'Availability updated',
            'is_available' => $driver->is_available,
        ]);
    }
}
