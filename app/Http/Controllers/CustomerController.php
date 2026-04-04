<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    //list All Customer
    public function index() {
        // $customerRole= Role::where('name','customer')->first();
        // // dd($customerRole);
        // $customers = User::where('role_id',$customerRole->id)->get();

        $customerRole = Role::where('name', 'customer')->first();

        $customers = collect();

       if ($customerRole) {
            $customers = User::where('role_id', $customerRole->id)->get();
        }
        // إرسال البيانات إلى Blade
        return view('customer.index', compact('customers'));

    }

    // Show create form
    public function create() {
        return view('customer.create');
    }

    // Store new customer
    public function store(Request $request) {
        $request->validate([
            'first_name'=>'required',
            'last_name'=>'required',
            'email'=>'required|email|unique:users',
            // 'password'=>'required|min:6',
        ]);

        $customerRole = Role::where('name','customer')->first();

        User::create([
            'first_name'=>$request->first_name,
            'second_name'=>$request->second_name,
            'last_name'=>$request->last_name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
            'role_id'=>$customerRole->id,
            'phone'=>$request->phone,
        ]);

        return redirect()->route('customers.index')->with('success','Customer created successfully!');
    }

    public function show($id){
        // dd($id);
        $customerRole = Role::where('name','customer')->first();
        // dd($customerRole);
        $customer = User::where('role_id', $customerRole->id)
                    ->where('id', $id)
                    ->firstOrFail();

        return view('customer.show', compact('customer'));

    }


    public function edit($id){

        $customerRole = Role::where('name','customer')->first();
        $customer = User::where('role_id', $customerRole->id)
                    ->where('id', $id)
                    ->firstOrFail();
        return view('customer.edit',compact('customer'));
    }

    public function update(CustomerRequest $request,$id){
        $customer = User::findOrFail($id);
        // dd($request);
        // $request->validate([
        //     'email'=>'required|email|unique:users',
        // ]);

        $customer->update([
            // 'first_name'=>$request->first_name,
            // 'second_name'=>$request->second_name,
            // 'last_name'=>$request->last_name,
            'email'=>$request->email,
            // 'password'=>bcrypt($request->password),
            // 'role_id'=>$customerRole->id,
            'phone'=>$request->phone,
            'address'=>$request->address,
        ]);
        return redirect()->route('customers.index')->with('success','Customer Updated successfully!');
    }

    public function destroy($id){
        // dd($id);
        $customer = User::findOrFail($id);
        // dd($customer->first_name);
        $customer->delete();

        return redirect()->route('customers.index')->with('success','Customer Deleted successfully!');

    }


    public function restore($id)
    {
        $customer = User::withTrashed()->findOrFail($id);
        $customer->restore();

    return redirect()->route('customers.index')
                     ->with('success', 'Customer restored successfully!');
}



}
