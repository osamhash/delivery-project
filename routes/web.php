<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use Illuminate\Support\Facades\Route;

// Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
// Route::post('/login', [AuthController::class, 'login'])->name('login.submit');

// Route::prefix('customers')->group(function() { //                                customer.dashboard
//         Route::get('/dashboard', [CustomerController::class,'dashboard'])->name('customers.dashboard');
//         Route::get('/', [CustomerController::class,'index'])->name('customers.index');
//         Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
//         Route::post('/', [CustomerController::class,'store'])->name('customers.store');
//         Route::get('/show/{id}', [CustomerController::class,'show'])->name('customers.show');
//         Route::get('/{id}/edit', [CustomerController::class,'edit'])->name('customers.edit');
//         Route::match(['put','patch'],'/{id}', [CustomerController::class,'update'])->name('customers.update');
//         Route::delete('/{id}/delete', [CustomerController::class,'destroy'])->name('customers.destroy');
// });

// Route::prefix('admins')->group(function() { //                                customer.dashboard
//     Route::get('/dashboard', [CustomerController::class,'dashboard'])->name('admins.dashboard');
// });

// Route::prefix('providers')->group(function() { //                                customer.dashboard
//     Route::get('/dashboard', [CustomerController::class,'dashboard'])->name('providers.dashboard');
// });

// Route::middleware('auth:sanctum')->group(function () {

//     Route::prefix('customer')->group(function () {

//         Route::get('/profile', [CustomerController::class, 'profile']);
//         Route::put('/profile', [CustomerController::class, 'updateProfile']);

//         Route::get('/orders', [CustomerController::class, 'orders']);

//         Route::post('/reviews', [CustomerController::class, 'storeReview']);

//         Route::get('/favorites', [CustomerController::class, 'favorites']);

//     });

// });
