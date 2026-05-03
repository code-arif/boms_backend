<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderSessionController;
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

        // ── Company Admin ──────────────────────────────────────────
        Route::middleware('role:company_admin,super_admin')->group(function () {

            // Menu items
            Route::apiResource('menu-items', MenuItemController::class);
            Route::patch(
                'menu-items/{menuItem}/toggle-availability',
                [MenuItemController::class, 'toggleAvailability']
            );

            // Order sessions
            Route::get('order-sessions',            [OrderSessionController::class, 'index']);
            Route::post('order-sessions',           [OrderSessionController::class, 'open']);
            Route::get('order-sessions/{id}',       [OrderSessionController::class, 'show']);
            Route::patch('order-sessions/{id}/close', [OrderSessionController::class, 'close']);

            // Admin order management
            Route::get('order-sessions/{session}/orders', [OrderController::class, 'sessionOrders']);
            Route::patch('orders/{order}/status',          [OrderController::class, 'updateStatus']);
        });

        // ── Employee + above ───────────────────────────────────────
        Route::middleware('role:employee,company_admin,super_admin')->group(function () {

            // Browse menu
            Route::get('menu-items', [MenuItemController::class, 'index']);
            Route::get('menu-items/{id}', [MenuItemController::class, 'show']);

            // Active session check
            Route::get('order-sessions/active', [OrderSessionController::class, 'active']);

            // Place / manage own orders
            Route::post('orders',                 [OrderController::class, 'store']);
            Route::get('orders/my',              [OrderController::class, 'myOrders']);
            Route::put('orders/{order}',         [OrderController::class, 'update']);
            Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel']);
        });
    });
});
