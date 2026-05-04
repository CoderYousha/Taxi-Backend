<?php

use App\Http\Controllers\LocationController;
use App\Http\Controllers\RequestController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ComplaintController;
use App\Events\MessagePosted;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CarTypeController;
use App\Http\Controllers\DiscountController;
use App\Http\Controllers\DriverController;

/*Route::post('/broadcast',function(){
    broadcast(new MessagePosted('hello from Postman2'));
    return response()->json(['status'=>'sent']);
});*/

Route::post('/broadcast', function (Request $request) {
    Log::info('Route HIT');

    broadcast(new MessagePosted($request));

    Log::info('After broadcast');

    return response()->json(['ok' => true]);
});

//////////////////////////////////////////

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', [UserController::class, 'login']);
Route::post('/user/update', [UserController::class, 'update'])->middleware('auth:sanctum');
Route::post('/addEmployee', [UserController::class, 'addEmployee'])->middleware('auth:sanctum');
Route::post('/updateEmployee', [UserController::class, 'updateEmployee'])->middleware('auth:sanctum');
Route::post('/deleteEmployee', [UserController::class, 'deleteEmployee'])->middleware('auth:sanctum');
Route::get('/getEmployee', [UserController::class, 'getEmployee'])->middleware('auth:sanctum');
Route::post('/register', [UserController::class, 'register']);
Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');
Route::post('/changePassword', [UserController::class, 'changePassword'])->middleware('auth:sanctum');
Route::post('/getProfile', [UserController::class, 'getProfile'])->middleware('auth:sanctum');
Route::get('/test', function () {
    return 'API working';
});


Route::prefix('car-types')->group(function () {
    // العمليات الأساسية
    Route::get('/index', [CarTypeController::class, 'index']);           // GET /api/car-types
    Route::post('/store', [CarTypeController::class, 'store']);          // POST /api/car-types
    Route::get('/show/{id}', [CarTypeController::class, 'show']);        // GET /api/car-types/{id}
    Route::put('/update', [CarTypeController::class, 'update']);      // PUT /api/car-types/{id}
    Route::delete('/destroy/{id}', [CarTypeController::class, 'forceDelete']);  // DELETE /api/car-types/{id}

    // عمليات soft delete
    Route::get('/trashed/all', [CarTypeController::class, 'trashed']);           // GET /api/car-types/trashed/all
    Route::post('/{id}/restore', [CarTypeController::class, 'restore']);         // POST /api/car-types/{id}/restore
    Route::delete('/{id}/force', [CarTypeController::class, 'forceDelete']);     // DELETE /api/car-types/{id}/force
});
Route::prefix('drivers')->group(function () {
    // العمليات الأساسية
    Route::post('/store', [DriverController::class, 'store'])->middleware('auth:sanctum');
    Route::get('/getImage/{path}', [DriverController::class, 'getImage']);
    Route::post('/updateLocation', [DriverController::class, 'updateLocation'])->middleware('auth:sanctum');
    Route::get('/active', [DriverController::class, 'active']);
    Route::get('/index', [DriverController::class, 'index']);
    Route::get('/show/{id}', [DriverController::class, 'show']);
    Route::post('/update/{id}', [DriverController::class, 'update']);
    Route::delete('/destroy/{id}', [DriverController::class, 'destroy']);

    // عمليات soft delete
    Route::get('/trashed/all', [DriverController::class, 'trashed']);
    Route::post('/{id}/restore', [DriverController::class, 'restore']);
    Route::delete('/{id}/force', [DriverController::class, 'forceDelete']);
});
Route::prefix('discounts')->group(function () {
    // العمليات الأساسية
    Route::get('/index', [DiscountController::class, 'index']);
    Route::post('/store', [DiscountController::class, 'store']);
    Route::get('/show/{id}', [DiscountController::class, 'show']);
    Route::post('/update/{id}', [DiscountController::class, 'update']);
    Route::delete('/destroy/{id}', [DiscountController::class, 'destroy']);

    Route::get('/by-code/{code}', [DiscountController::class, 'findByCode']);
    Route::post('/validate', [DiscountController::class, 'validateAndApply']);
    Route::post('/confirm', [DiscountController::class, 'confirmUsage']);

    // إحصائيات
    Route::get('/statistics/summary', [DiscountController::class, 'statistics']);

    // عمليات soft delete
    Route::get('/trashed/all', [DiscountController::class, 'trashed']);
    Route::post('/{id}/restore', [DiscountController::class, 'restore']);
    Route::delete('/{id}/force', [DiscountController::class, 'forceDelete']);
});

Route::prefix('locations')->group(function () {
    // العمليات الأساسية
    Route::get('/index', [LocationController::class, 'index']);
    Route::post('/store', [LocationController::class, 'store']);
    Route::post('/bulk', [LocationController::class, 'bulkStore']);
    Route::get('/show/{id}', [LocationController::class, 'show']);
    Route::post('/update/{id}', [LocationController::class, 'update']);
    Route::delete('/destroy/{id}', [LocationController::class, 'destroy']);

    // عمليات إضافية
    Route::post('/nearby/search', [LocationController::class, 'nearby']);
    Route::post('/distance/calculate', [LocationController::class, 'calculateDistance']);
    Route::get('/popular/pickup', [LocationController::class, 'popularPickupLocations']);
    Route::get('/popular/dropoff', [LocationController::class, 'popularDropoffLocations']);

    // عمليات soft delete
    Route::get('/trashed/all', [LocationController::class, 'trashed']);
    Route::post('/{id}/restore', [LocationController::class, 'restore']);
    Route::delete('/{id}/force', [LocationController::class, 'forceDelete']);
});

// Routes للطلبات
Route::prefix('requests')->group(function () {
    Route::post('/store', [RequestController::class, 'store']);
    Route::get('/available-bookings', [RequestController::class, 'getAvailableBookings']);
    Route::post('/{requestId}/accept', [RequestController::class, 'acceptBooking']);
    Route::post('/{requestId}/start', [RequestController::class, 'startTrip']);
    Route::post('/{requestId}/finish', [RequestController::class, 'finishTrip']);
    Route::post('/{requestId}/remind', [RequestController::class, 'remindDriver']);
    Route::get('/user/{userId}', [RequestController::class, 'getUserRequests']);
    Route::get('/driver/{driverId}/trips', [RequestController::class, 'getDriverTrips']);
});

// Routes للتقارير
Route::prefix('reports')->group(function () {
    Route::get('/financial', [ReportController::class, 'financialReport']);
    Route::get('/operational', [ReportController::class, 'operationalReport']);
    Route::get('/quality', [ReportController::class, 'qualityReport']);
});

Route::prefix('complaints')->group(function () {
    // العمليات الأساسية
    Route::get('/', [ComplaintController::class, 'index']);
    Route::post('/', [ComplaintController::class, 'store']);
    Route::get('/{id}', [ComplaintController::class, 'show']);
    Route::put('/{id}/resolve', [ComplaintController::class, 'resolve']);
    Route::delete('/{id}', [ComplaintController::class, 'destroy']);

    // عمليات إضافية
    Route::get('/driver/{driverId}', [ComplaintController::class, 'getDriverComplaints']);
    Route::get('/request/{requestId}', [ComplaintController::class, 'getRequestComplaints']);
    Route::get('/statistics/summary', [ComplaintController::class, 'statistics']);

    // عمليات soft delete
    Route::post('/{id}/restore', [ComplaintController::class, 'restore']);
});
