<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me', [AuthController::class, 'me']);

        // Super Admin only
        Route::middleware('role:super_admin')->prefix('admin')->group(function () {
            Route::apiResource('companies', CompanyController::class);
            Route::post('companies/{company}/assign-admin', [CompanyController::class, 'assignAdmin']);
            Route::patch('companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus']);

            // Super admin user management (all tenants)
            Route::apiResource('users', UserController::class);
        });

        // ── Company Admin ──────────────────────────────────
        Route::middleware('role:company_admin')->group(function () {
            // Manage own employees
            Route::apiResource('users', UserController::class)->except('index')->names([
                'store'   => 'users.store.admin',
                'show'    => 'users.show.admin',
                'update'  => 'users.update.admin',
                'destroy' => 'users.destroy.admin',
            ]);
            Route::get('users', [UserController::class, 'index'])->name('users.index.admin');
            Route::patch('users/{user}/toggle-active',    [UserController::class, 'toggleActive']);

            // Teams
            Route::apiResource('teams', TeamController::class);
            Route::post('teams/{team}/assign-user',       [TeamController::class, 'assignUser']);
        });

        // Employee + above
        Route::middleware('role:employee,company_admin,super_admin')->group(function () {
            // Phase 3+ routes go here
        });
    });
});
