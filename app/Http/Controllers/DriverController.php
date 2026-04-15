<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DriverController extends Controller
{
    public function index()
    {

    //     $drivers = Driver::join('users', 'drivers.user_id', '=', 'users.id')
    // ->select(
    //     'drivers.*',         // كل أعمدة السائق
    //     'users.first_name',
    //     'users.last_name',
    //     'users.email',
    //     'users.phone',
    //     'users.role_id'
    // )
    // ->get();


        $drivers = Driver::with('user')->get(); // جلب السائقين مع معلومات المستخدم
       dd($drivers->toArray());
        return view('drivers.index', compact('drivers'));
    }


    public function create()
{
    $users = User::where('role_id','!=','1')->where('role_id','!=','2')->where('role_id','!=','4')
                 ->get();

    return view('drivers.create', compact('users'));
}


    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'first_name' => 'required_without:user_id|string|max:100',
            'last_name'  => 'required_without:user_id|string|max:100',
            'email'      => 'required_without:user_id|email|unique:users,email',
            'password'   => 'required_without:user_id|string|min:6|confirmed',
            'is_available' => 'required|boolean',
        ]);

        DB::transaction(function () use ($request) {
            if ($request->user_id) {
                // المستخدم موجود مسبقًا
                $user = User::findOrFail($request->user_id);
            } else {
                // إنشاء مستخدم جديد
                $user = User::create([
                    'first_name' => $request->first_name,
                    'last_name'  => $request->last_name,
                    'email'      => $request->email,
                    'password'   => Hash::make($request->password),
                    'role_id'    => 3, // Driver
                ]);
            }

            // إنشاء السائق وربطه بالمستخدم
            Driver::create([
                'user_id' => $user->id,
                'is_available' => $request->is_available,

            ]);
        });

        return redirect()->route('drivers.index')->with('success', 'تم إنشاء السائق بنجاح');
    }

    public function edit(Driver $driver)
    {
        $users = User::where('role_id', 3)->get();
        return view('drivers.edit', compact('driver', 'users'));
    }

    public function update(Request $request, Driver $driver)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'is_available' => 'required|boolean',
        ]);

        $driver->update($request->all());
        return redirect()->route('drivers.index')->with('success', 'تم تحديث بيانات السائق');
    }

    public function destroy(Driver $driver)
    {
        $driver->delete();
        return redirect()->route('drivers.index')->with('success', 'تم حذف السائق');
    }
}
