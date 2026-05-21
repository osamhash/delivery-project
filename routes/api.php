<?php

// use App\Http\Controllers\Api\DriverController;
// use App\Http\Controllers\Api\NotificationController;
// use App\Http\Controllers\Api\PaymentController;
// use App\Http\Controllers\Api\ReviewController;

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DriverController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProviderController;
use App\Http\Controllers\ReviewController ;
use App\Http\Controllers\PaymentController ;
use App\Models\Driver;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);

    Route::post('/reset-password', [AuthController::class, 'resetPassword']);
});

Route::get('/test', function () {

    return response()->json([
        'message' => 'API WORKING'
    ]);
});

Route::get('/providers', [ProviderController::class, 'index']);

Route::get('/drivers/available', function () {

    return Driver::where('is_available', true)
        ->with('user')
        ->get();
});

Route::get('/statuses', [OrderController::class, 'statuses']);

/*
|--------------------------------------------------------------------------
| Protected Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Auth
    |--------------------------------------------------------------------------
    */

    Route::prefix('auth')->group(function () {

        Route::post('/logout', [AuthController::class, 'logout']);

        Route::get('/me', [AuthController::class, 'me']);
    });

    /*
    |--------------------------------------------------------------------------
    | Customer
    |--------------------------------------------------------------------------
    */

    Route::get('/customer/dashboard', [CustomerController::class, 'dashboard']);

    Route::get('/customer/profile', [CustomerController::class, 'show']);

    Route::put('/customer/profile', [CustomerController::class, 'update']);

    /*
    |--------------------------------------------------------------------------
    | Driver Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('driver')->group(function () {

        Route::get('/dashboard', [DriverController::class, 'dashboard']);

        Route::patch('/availability', [DriverController::class, 'updateAvailability']);

        Route::post('/orders/{order}/accept', [DriverController::class, 'acceptOrder']);

        Route::post('/orders/{order}/reject', [DriverController::class, 'rejectOrder']);

        Route::post('/orders/{order}/start', [DriverController::class, 'startDelivery']);

        Route::post('/orders/{order}/complete', [DriverController::class, 'completeDelivery']);

        Route::get('/orders/{order}', [DriverController::class, 'getOrderDetails']);

        Route::get('/profile', [DriverController::class, 'getProfile']);

        Route::put('/profile', [DriverController::class, 'updateProfile']);

        Route::get('/history', [DriverController::class, 'getDeliveryHistory']);
    });

    /*
    |--------------------------------------------------------------------------
    | Payment Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('payments')->group(function () {

        Route::post('/orders/{order}/cod', [PaymentController::class, 'payOnDelivery']);

        Route::get('/orders/{order}/status', [PaymentController::class, 'getPaymentStatus']);
    });

    /*
    |--------------------------------------------------------------------------
    | Review Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('reviews')->group(function () {

        Route::post('/orders/{order}', [ReviewController::class, 'store']);

        Route::get('/driver', [ReviewController::class, 'getDriverReviews']);
    });

    /*
    |--------------------------------------------------------------------------
    | Notification Routes
    |--------------------------------------------------------------------------
    */

    Route::prefix('notifications')->group(function () {

        // جميع الإشعارات
        Route::get('/', [NotificationController::class, 'index']);

        // عدد غير المقروء
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);

        // إنشاء إشعار
        Route::post('/', [NotificationController::class, 'store']);

        // تعليم إشعار كمقروء
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);

        // تعليم الكل كمقروء
        Route::post('/read-all', [NotificationController::class, 'markAllAsRead']);

        // حذف إشعار
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | Orders
    |--------------------------------------------------------------------------
    */
// osama
    // Route::get('/user/{id}/orders', [OrderController::class, 'userOrders']);

    // Route::get('/orders', [OrderController::class, 'index']);

    // Route::post('/orders', [OrderController::class, 'store']);

    // Route::get('/orders/{order}', [OrderController::class, 'show']);

    // Route::put('/orders/{id}', [OrderController::class, 'update']);

    // Route::delete('/orders/{id}', [OrderController::class, 'destroy']);

    /*
    |--------------------------------------------------------------------------
    | Providers
    |--------------------------------------------------------------------------
    */

    Route::get('/provider/{id}/orders', [OrderController::class, 'providerOrders']);

    Route::get('/providers/{provider}/products', [ProductController::class, 'byProvider']);

    /*
    |--------------------------------------------------------------------------
    | Favorites
    |--------------------------------------------------------------------------
    */

    Route::get('/favorites', [CustomerController::class, 'favorites']);

    Route::post('/favorites/{provider}', [CustomerController::class, 'toggleFavorite']);
});


// ------------------





    /*
    |--------------------------------------------------------------------------
    | Auth Routes (بدون حماية)
    |--------------------------------------------------------------------------
    */
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login',    [AuthController::class, 'login']);
    });

    /*
    |--------------------------------------------------------------------------
    | Protected Routes (تتطلب JWT Token)
    |--------------------------------------------------------------------------
    */
    // Route::middleware('auth:api')->group(function () {

    //     // Auth
    //     Route::prefix('auth')->group(function () {
    //         Route::post('/logout',          [AuthController::class, 'logout']);
    //         Route::get('/profile',          [AuthController::class, 'profile']);
    //         Route::put('/profile',          [AuthController::class, 'updateProfile']);
    //         Route::post('/refresh',         [AuthController::class, 'refresh']);
    //     });

    //     // Customers
    //     Route::prefix('customers')->group(function () {
    //         Route::get('/',                  [CustomerController::class, 'index']);
    //         Route::post('/',                 [CustomerController::class, 'store']);
    //         Route::get('/{id}',              [CustomerController::class, 'show']);
    //         Route::put('/{id}',              [CustomerController::class, 'update']);
    //         Route::delete('/{id}',           [CustomerController::class, 'destroy']);
    //         Route::put('/{id}/restore',      [CustomerController::class, 'restore']);
    //     });

    //     // Drivers
    //     Route::prefix('drivers')->group(function () {
    //         Route::get('/',                       [DriverController::class, 'index']);
    //         Route::get('/available',              [DriverController::class, 'available']);
    //         Route::post('/',                      [DriverController::class, 'store']);
    //         Route::get('/{id}',                   [DriverController::class, 'show']);
    //         Route::put('/{id}',                   [DriverController::class, 'update']);
    //         Route::delete('/{id}',                [DriverController::class, 'destroy']);
    //         Route::patch('/{id}/availability',    [DriverController::class, 'toggleAvailability']);
    //     });

    //     // Providers
    //     Route::prefix('providers')->group(function () {
    //         Route::get('/',               [ProviderController::class, 'index']);
    //         Route::post('/',              [ProviderController::class, 'store']);
    //         Route::get('/{id}',           [ProviderController::class, 'show']);
    //         Route::put('/{id}',           [ProviderController::class, 'update']);
    //         Route::delete('/{id}',        [ProviderController::class, 'destroy']);
    //         Route::get('/{id}/products',  [ProviderController::class, 'products']);
    //     });

    //     // Products
    //     Route::prefix('products')->group(function () {
    //         Route::get('/',        [ProductController::class, 'index']);
    //         Route::post('/',       [ProductController::class, 'store']);
    //         Route::get('/{id}',    [ProductController::class, 'show']);
    //         Route::put('/{id}',    [ProductController::class, 'update']);
    //         Route::delete('/{id}', [ProductController::class, 'destroy']);
    //     });

    //     // Orders
    //     Route::prefix('orders')->group(function () {
    //         Route::get('/',                       [OrderController::class, 'index']);
    //         Route::post('/',                      [OrderController::class, 'store']);
    //         Route::get('/statuses',               [OrderController::class, 'statuses']);
    //         Route::get('/{id}',                   [OrderController::class, 'show']);
    //         Route::put('/{id}',                   [OrderController::class, 'update']);
    //         Route::delete('/{id}',                [OrderController::class, 'destroy']);
    //         Route::get('/user/{userId}',          [OrderController::class, 'userOrders']);
    //         Route::get('/provider/{providerId}',  [OrderController::class, 'providerOrders']);
    //     });

    //     // Payments
    //     Route::prefix('payments')->group(function () {
    //         Route::get('/',                      [PaymentController::class, 'index']);
    //         Route::post('/',                     [PaymentController::class, 'store']);
    //         Route::get('/{id}',                  [PaymentController::class, 'show']);
    //         Route::get('/order/{orderId}',       [PaymentController::class, 'orderPayment']);
    //         Route::get('/user/{userId}',         [PaymentController::class, 'userPayments']);
    //     });

    //     // Reviews
    //     Route::prefix('reviews')->group(function () {
    //         Route::get('/',                         [ReviewController::class, 'index']);
    //         Route::post('/',                        [ReviewController::class, 'store']);
    //         Route::get('/{id}',                     [ReviewController::class, 'show']);
    //         Route::put('/{id}',                     [ReviewController::class, 'update']);
    //         Route::delete('/{id}',                  [ReviewController::class, 'destroy']);
    //         Route::get('/provider/{providerId}',    [ReviewController::class, 'providerReviews']);
    //     });
    // });


    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('customers')->group(function () {
            Route::get('/', [CustomerController::class, 'index']);
            Route::post('/', [CustomerController::class, 'store']);
            Route::get('/{id}', [CustomerController::class, 'show']);
            Route::put('/{id}', [CustomerController::class, 'update']);
            Route::delete('/{id}', [CustomerController::class, 'destroy']);
        });

    });

    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });



Route::prefix('auth')->group(function () {
    Route::post('register',        [AuthController::class, 'register']);
    Route::post('login',           [AuthController::class, 'login']);
    Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('reset-password',  [AuthController::class, 'resetPassword']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me',      [AuthController::class, 'me']);
    });
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API WORKING'
    ]);
});
Route::middleware('auth:sanctum')->get('/customer/dashboard', [CustomerController::class, 'dashboard']);

Route::get('/providers', [ProviderController::class, 'index']);


Route::get('/orders', [OrderController::class, 'index']);          // عرض كل الطلبات
Route::post('/orders', [OrderController::class, 'store']);
Route::post('/orders/preview', [OrderController::class, 'preview']);      // إنشاء طلب
// Route::get('/orders/{id}', [OrderController::class, 'show']);      // عرض طلب واحد
Route::put('/orders/{id}', [OrderController::class, 'update']);    // تحديث الطلب
Route::delete('/orders/{id}', [OrderController::class, 'destroy']); // حذف الطلب



Route::get('/drivers/available', function () {
    return Driver::where('is_available', true)->with('user')->get();
});


//حسب المستخدم
Route::get('/user/{id}/orders', [OrderController::class, 'userOrders']);

//حسب المتجر
Route::get('/provider/{id}/orders', [OrderController::class, 'providerOrders']);

Route::get('/statuses', [OrderController::class, 'statuses']);



// Route::get('/providers', [ProviderController::class, 'index']);

Route::get('/drivers/available', [DriverController::class, 'available']);




Route::prefix('v1')->group(function () {

    // ── Users ──────────────────────────────────────────────────
    Route::get('users/{user}', [CustomerController::class, 'show']);

    // ── Providers → Products ───────────────────────────────────
    Route::get('providers/{provider}/products', [ProductController::class, 'byProvider']);

    // ── Drivers ────────────────────────────────────────────────
    Route::get('drivers',           [DriverController::class, 'index']);          // all drivers
    Route::get('drivers/available', [DriverController::class, 'available']);      // only available

    // ── Orders ─────────────────────────────────────────────────
    Route::post('orders',            [OrderController::class, 'store']);
    Route::get('orders/{order}',     [OrderController::class, 'show']);
    Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);
});

Route::get('providers',        [ProviderController::class, 'index']);   // يدعم ?search= و ?type=
Route::get('providers/stats',  [ProviderController::class, 'stats']);   // إحصائيات سريعة

