<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']);

    // Protected routes
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/auth/me',[AuthController::class, 'me']);

        // Super Admin only
        Route::middleware('role:super_admin')->prefix('admin')->group(function () {
            // Phase 5 routes go here
        });

        // Company Admin
        Route::middleware('role:company_admin,super_admin')->group(function () {
            // Phase 2+ routes go here
        });

        // Employee + above
        Route::middleware('role:employee,company_admin,super_admin')->group(function () {
            // Phase 3+ routes go here
        });
    });
});
