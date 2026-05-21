<?php

namespace App\Http\Controllers;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard(Request $request) {
        $customers = CustomerController::index();
        $drivers = DriverController::index();
        $order = OrderController::index();

    }



}
