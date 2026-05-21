<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Provider;
use App\Http\Requests\CustomerRequest;
use App\Http\Requests\ProviderRequest;
use App\Http\Resources\ProviderResource;
use App\Models\Role;
use App\Models\User;
use GuzzleHttp\Handler\Proxy;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProviderController extends Controller
{

    public function products($id, Request $request)
    {
        $query = Product::where('provider_id', $id);
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }
        return $query->get();
    }


    // list All Provider
   public function index()
{
    $providers = Provider::with('user')->get();

    foreach ($providers as $provider) {
        if ($provider->user && $provider->user->image_path) {
            $provider->user->image_url = asset('storage/' . $provider->user->image_path);
        } else {
            $provider->user->image_url = null;
        }
    }

    return response()->json($providers);
}
/**
     * GET /api/providers
     * Supports: ?search=, ?type=, ?per_page=
     */
    // public function index(ProviderRequest $request)
    // {
    //     $query = Provider::with(['user', 'products'])
    //         ->withCount('orders');                    // عدد الطلبات لكل provider

    //     // ── بحث بالاسم أو النوع ──────────────────────────────
    //     if ($search = $request->search) {
    //         $query->where(function ($q) use ($search) {
    //             $q->where('type', 'like', "%{$search}%")
    //               ->orWhereHas('user', function ($u) use ($search) {
    //                   $u->where('first_name', 'like', "%{$search}%")
    //                     ->orWhere('last_name',  'like', "%{$search}%");
    //               });
    //         });
    //     }

    //     // ── فلتر بالنوع ───────────────────────────────────────
    //     if ($type = $request->type) {
    //         $query->where('type', $type);
    //     }

    //     $providers = $query->latest()->get();
    //     $r = ProviderResource::collection($providers);
    //     return $r;
    // }

    // Show create form
    public function create() {
        return view('providers.create');
    }

    // Store new customer
    public function store(Request $request) {
        // $request->validate([
        //     'first_name'=>'required',
        //     'last_name'=>'required',
        //     'email'=>'required|email|unique:users',
        //     // 'password'=>'required|min:6',
        // ]);

        $providerRole = Role::where('name','provider')->first();

        User::create([
            'first_name'=>$request->first_name,
            'second_name'=>$request->second_name,
            'last_name'=>$request->last_name,
            'email'=>$request->email,
            'password'=>bcrypt($request->password),
            'role_id'=>$providerRole->id,
            'phone'=>$request->phone,
        ]);

        dd();
        //الحين كيف بدنا انجيب ال user_id  الخاص ب ال  new User
        // $user_id = User::id;
        Provider::create([
            'user_id'=>$user_id,
            'type'=>$request->type,
        ]);

        return redirect()->route('providers.index')->with('success','providers created successfully!');
    }

    public function show($id){
        $provider = Provider::with('user')->where('id',$id)->firstOrFail();
        dd($provider->toArray());
        return view('providers.index', compact('provider'));
    }


    public function edit($id){
    $provider = Provider::join('users', 'providers.user_id', '=', 'users.id')
        ->select('providers.*',
        'users.first_name',
        'users.second_name',
        'users.last_name',
        'users.email',
        'users.phone',
        'users.role_id',
        'users.address',
        'users.date_of_birth')->first();
        return view('providers.edit',compact('provider'));
    }

    public function update(CustomerRequest $request,$id){
        $provider = Provider::join('users', 'providers.user_id', '=', 'users.id')
        ->select('providers.*',
        'users.first_name',
        'users.second_name',
        'users.last_name',
        'users.email',
        'users.phone',
        'users.address',
        'users.date_of_birth')->first();
       // dd($request->all(),$id);
        $provider->update([
            'first_name'=>$request->first_name,
            'second_name'=>$request->second_name,
            'last_name'=>$request->last_name,
            'email'=>$request->email,
            // 'password'=>bcrypt($request->password),
            //'role_id'=>$customerRole->id,
            'phone'=>$request->phone,
            'address'=>$request->address,
            'date_of_birth'=>$request->date_of_birth,
        ]);
        dd($provider);
        return redirect()->route('providers.index')->with('success','providers Updated successfully!');
    }

    public function destroy($id){
        dd($id);
        $provider = Provider::findOrFail($id);
        dd($provider->first_name);
        $provider->delete();

        return redirect()->route('providers.index')->with('success','Provider Deleted successfully!');

    }

    public function dashboard(Request $request):View{
        return view('dashboard');
    }
}
