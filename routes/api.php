    <?php

    use App\Http\Controllers\AuthController;
    use App\Http\Controllers\CustomerController;
    use App\Http\Controllers\DriverController;
    use App\Http\Controllers\OrderController;
    use App\Http\Controllers\PaymentController;
    use App\Http\Controllers\ProductController;
    use App\Http\Controllers\ProviderController;
    use App\Http\Controllers\ReviewController;
    use Illuminate\Support\Facades\Route;

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
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
});

Route::get('/test', function () {
    return response()->json([
        'message' => 'API WORKING'
    ]);
});
Route::middleware('auth:sanctum')->get('/customer/dashboard', [CustomerController::class, 'dashboard']);
