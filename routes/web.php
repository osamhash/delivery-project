<?php

use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::get('/list', function () {
//     return view('welcome');
// });
Route::prefix('customers')->group(function() {
        Route::get('/', [CustomerController::class,'index'])->name('customers.index');
        Route::get('/create', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/', [CustomerController::class,'store'])->name('customers.store');
        Route::get('/show/{id}', [CustomerController::class,'show'])->name('customers.show');

        Route::get('/{id}/edit', [CustomerController::class,'edit'])->name('customers.edit');
        Route::match(['put','patch'],'/{id}', [CustomerController::class,'update'])->name('customers.update');


        Route::delete('/{id}/delete', [CustomerController::class,'destroy'])->name('customers.destroy');



        });

