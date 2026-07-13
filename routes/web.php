<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\TwoFactorSetupController;
use App\Http\Controllers\BuildingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\TenantPortalController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VisitController;
use App\Http\Controllers\VisitorTypeController;
use Illuminate\Support\Facades\Route;

// Guest routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
    Route::get('/2fa/challenge', [LoginController::class, 'showTwoFactorChallenge'])->name('2fa.challenge');
    Route::post('/2fa/challenge', [LoginController::class, 'twoFactorChallenge']);
});

// Authenticated routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // 2FA Setup (accessible before 2FA is enabled)
    Route::get('/2fa/setup', [TwoFactorSetupController::class, 'show'])->name('2fa.setup');
    Route::post('/2fa/enable', [TwoFactorSetupController::class, 'enable'])->name('2fa.enable');
    Route::post('/2fa/confirm', [TwoFactorSetupController::class, 'confirm'])->name('2fa.confirm');
    Route::delete('/2fa/disable', [TwoFactorSetupController::class, 'disable'])->name('2fa.disable');

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Admin routes
        Route::middleware('role:super_admin|building_manager')->group(function () {
            // Buildings
            Route::resource('buildings', BuildingController::class)->except('show');

            // Units
            Route::resource('units', UnitController::class)->except('show');

            // Users
            Route::resource('users', UserController::class)->except('show');
            Route::put('/users/{user}/working-hours', [UserController::class, 'workingHours'])->name('users.working-hours');

            // Visitor Types
            Route::resource('visitor-types', VisitorTypeController::class)->except('show', 'create', 'edit');

            // Shifts
            Route::resource('shifts', ShiftController::class)->except('show', 'create', 'edit');

            // Visits (admin)
            Route::resource('visits', VisitController::class)->except('show', 'edit', 'update');
            Route::get('/visits/{visit}/confirmation', [VisitController::class, 'confirmation'])->name('visits.confirmation');

            // Dashboard analytics drill-down
            Route::prefix('dashboard')->name('dashboard.')->group(function () {
                Route::get('/visitors/with-vehicle', [DashboardController::class, 'visitorsWithVehicle'])->name('visitors.with-vehicle');
                Route::get('/visitors/without-vehicle', [DashboardController::class, 'visitorsWithoutVehicle'])->name('visitors.without-vehicle');
            });

            // Reports
            Route::prefix('reports')->name('reports.')->group(function () {
                Route::get('/activity-log', [ReportController::class, 'activityLog'])->name('activity-log');
                Route::get('/visitor-activity', [ReportController::class, 'visitorActivity'])->name('visitor-activity');
                Route::get('/tenant-activity', [ReportController::class, 'tenantActivity'])->name('tenant-activity');
            });
        });

        // Tenant portal
        Route::middleware('role:tenant')->prefix('portal')->name('tenant.')->group(function () {
            Route::get('/', [TenantPortalController::class, 'dashboard'])->name('dashboard');
            Route::post('/visitors', [TenantPortalController::class, 'preRegisterVisitor'])->name('register-visitor');
            Route::get('/history', [TenantPortalController::class, 'visitHistory'])->name('history');
            Route::get('/visits/{visit}/confirmation', [TenantPortalController::class, 'visitConfirmation'])->name('visits.confirmation');
            Route::delete('/visits/{visit}/cancel', [TenantPortalController::class, 'cancelVisit'])->name('cancel-visit');
        });
});
