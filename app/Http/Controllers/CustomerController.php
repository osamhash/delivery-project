<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    //get alll customers
    public function index()
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
    public function show($id)
    {
        $role = Role::where('name', 'customer')->first();

        $customer = User::where('role_id', $role->id)
            ->where('id', $id)
            ->first();

        if (!$customer) {
            return response()->json([
                'message' => 'Customer not found'
            ], 404);
        }

        return response()->json([
            'data' => $customer
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
