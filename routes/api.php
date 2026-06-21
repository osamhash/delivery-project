<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ProviderDashboardController;
use App\Http\Controllers\ReviewController;
use App\Models\Driver;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (No Authentication Required)
|--------------------------------------------------------------------------
*/

// Test endpoint
Route::get('/test', function () {
    return response()->json(['message' => 'API WORKING']);
});

// Auth
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

// Public provider routes
Route::get('/providers', [ProviderController::class, 'index']);
Route::get('/providers/{provider}/products', [ProductController::class, 'byProvider']);

// Public driver routes
Route::get('/drivers/available', function () {
    return Driver::where('is_available', true)->with('user')->get();
});

// Order statuses
Route::get('/statuses', [OrderController::class, 'statuses']);

/*
|--------------------------------------------------------------------------
| Protected Routes (Requires Authentication)
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    // ========== AUTH ==========
    Route::prefix('auth')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/me', [AuthController::class, 'me']);
    });

    // ========== CUSTOMER ==========
    Route::prefix('customer')->group(function () {
        Route::get('/dashboard', [CustomerController::class, 'dashboard']);
        Route::get('/profile', [CustomerController::class, 'show']);
        Route::put('/profile', [CustomerController::class, 'update']);
    });

    // ========== DRIVER ==========
    Route::prefix('driver')->group(function () {
        Route::get('/dashboard', [DriverController::class, 'dashboard']);
        Route::patch('/availability', [DriverController::class, 'updateAvailability']);
        Route::get('/profile', [DriverController::class, 'getProfile']);
        Route::put('/profile', [DriverController::class, 'updateProfile']);
        Route::get('/history', [DriverController::class, 'getDeliveryHistory']);

        // Order actions
        Route::post('/orders/{order}/accept', [DriverController::class, 'acceptOrder']);
        Route::post('/orders/{order}/reject', [DriverController::class, 'rejectOrder']);
        Route::post('/orders/{order}/start', [DriverController::class, 'startDelivery']);
        Route::post('/orders/{order}/complete', [DriverController::class, 'completeDelivery']);
        Route::post('/orders/{order}/cancel', [DriverController::class, 'cancelOrder']);
        Route::post('/orders/{order}/confirm-cash', [DriverController::class, 'confirmCashPayment']);
        Route::get('/orders/{order}', [DriverController::class, 'getOrderDetails']);
    });

    // ========== ORDERS ==========
    Route::prefix('orders')->group(function () {
        Route::post('/', [OrderController::class, 'store']);
        Route::get('/{order}', [OrderController::class, 'show']);  // ✅ هذا الroute المفقود
        Route::put('/{order}', [OrderController::class, 'update']);
        Route::delete('/{order}', [OrderController::class, 'destroy']);
        Route::patch('/{order}/status', [OrderController::class, 'updateStatus']);
    });

    // User orders (customer)
    Route::get('/user/{id}/orders', [OrderController::class, 'userOrders']);

    // Provider orders
    Route::get('/provider/{id}/orders', [OrderController::class, 'providerOrders']);

    // ========== PAYMENTS ==========
    Route::prefix('payments')->group(function () {
        Route::post('/orders/{order}/cod', [PaymentController::class, 'payOnDelivery']);
        Route::get('/orders/{order}/status', [PaymentController::class, 'getPaymentStatus']);
    });

    // ========== REVIEWS ==========
    Route::prefix('reviews')->group(function () {
        Route::post('/', [ReviewController::class, 'store']);  // ✅ POST /api/reviews
        Route::get('/driver', [ReviewController::class, 'driverReviews']);
        Route::get('/provider', [ReviewController::class, 'providerReviews']);
        Route::get('/provider/rating', [ReviewController::class, 'providerRating']);
    });

    // ========== NOTIFICATIONS ==========
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);
    });

    // ========== FAVORITES ==========
    Route::get('/favorites', [CustomerController::class, 'favorites']);
    Route::post('/favorites/{provider}', [CustomerController::class, 'toggleFavorite']);

    // ========== PROVIDER DASHBOARD ==========
    Route::prefix('provider')->group(function () {
        Route::get('/dashboard/stats', [ProviderDashboardController::class, 'stats']);
        Route::get('/dashboard/recent-orders', [ProviderDashboardController::class, 'recentOrders']);
        Route::get('/dashboard/all-orders', [ProviderDashboardController::class, 'allOrders']);
        Route::get('/my-products', [ProductController::class, 'myProducts']);
        Route::post('/products', [ProductController::class, 'store']);
        Route::put('/products/{id}', [ProductController::class, 'update']);
        Route::delete('/products/{id}', [ProductController::class, 'destroy']);
        Route::put('/orders/{id}/status', [OrderController::class, 'updateProviderOrderStatus']);
        Route::get('/reviews', [ReviewController::class, 'providerReviews']);
        Route::get('/rating', [ReviewController::class, 'providerRating']);
    });

    // ========== MY PROVIDER ==========
    Route::get('/providers/me', [ProviderController::class, 'getMyProvider']);
    Route::put('/providers/{id}', [ProviderController::class, 'update']);

    // ========== ADMIN ROUTES (commented for now) ==========

    Route::middleware(['auth:api', 'admin'])->group(function () {

        // المستخدمين
        Route::get('/customers',     [AdminController::class, 'getCustomers']);

        Route::put('/users/{id}',    [AdminController::class, 'updateUser']);
        Route::delete('/users/{id}', [AdminController::class, 'deleteUser']);

        // الطلبات
        Route::get('/orders',           [AdminController::class, 'getOrders']);
        Route::put('/orders/{id}',      [AdminController::class, 'updateOrder']);
        Route::delete('/orders/{id}',   [AdminController::class, 'deleteOrder']);
        Route::match(['put', 'post'], '/admin/profile', [AdminController::class, 'updateAdminProfile']);
    });
});

// ========== V1 API Routes (Backward Compatibility) ==========
Route::prefix('v1')->group(function () {
    Route::get('drivers', [DriverController::class, 'index']);
    Route::get('drivers/available', [DriverController::class, 'available']);
    Route::get('providers/{provider}/products', [ProductController::class, 'byProvider']);
    Route::post('orders', [OrderController::class, 'store']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::get('users/{user}', [CustomerController::class, 'show']);
});
