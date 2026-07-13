<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\VisitApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    // Auth
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Visits
        Route::get('/visits/today', [VisitApiController::class, 'todayExpected']);
        Route::get('/visits/expected', [VisitApiController::class, 'expectedVisits']);
        Route::post('/visits/check-in', [VisitApiController::class, 'checkIn']);
        Route::put('/visits/{visit}/check-out', [VisitApiController::class, 'checkOut']);

        // Lookups
        Route::get('/lookup/national-id', [VisitApiController::class, 'lookupByNationalId']);
        Route::get('/lookup/plate', [VisitApiController::class, 'lookupByPlate']);
        Route::get('/lookup/qr', [VisitApiController::class, 'lookupByQr']);

        // Reference data
        Route::get('/units', [VisitApiController::class, 'getUnits']);
        Route::get('/visitor-types', [VisitApiController::class, 'getVisitorTypes']);

        // Shifts
        Route::post('/shifts/start', [VisitApiController::class, 'startShift']);
        Route::post('/shifts/end', [VisitApiController::class, 'endShift']);
    });
});
