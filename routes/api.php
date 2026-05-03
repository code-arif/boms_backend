<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CompanyController;
use App\Http\Controllers\Api\MenuItemController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\OrderSessionController;
use App\Http\Controllers\Api\Payment\PaymentController;
use App\Http\Controllers\Api\SuperAdmin\SuperAdminController;
use App\Http\Controllers\Api\TeamController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

// Health Check for the API
Route::get('/health', function () {
    return response()->json([
        'status' => true,
        'message' => 'BOMS API is running.',
        'data' => [
            'version' => '1.0.0',
            'timestamp' => now()->toIso8601String(),
        ],
    ]);
});

// V1 Routes
Route::prefix('v1')->group(function () {

    // Public routes
    Route::post('/auth/login', [AuthController::class, 'login']); // DONE: company admin, employee, super admin login

    // Protected routes
    Route::middleware(['auth:sanctum', 'active'])->group(function () {

        Route::post('/auth/logout', [AuthController::class, 'logout']); // DONE: company admin, employee, super admin logout
        Route::get('/auth/me', [AuthController::class, 'me']); // DONE: company admin, employee, super admin get authenticated user

        // Super Admin only
        Route::middleware('role:super_admin')->prefix('admin')->group(function () {
            Route::apiResource('companies', CompanyController::class);
            Route::post('companies/{company}/assign-admin', [CompanyController::class, 'assignAdmin']);
            Route::patch('companies/{company}/toggle-status', [CompanyController::class, 'toggleStatus']);

            // Super admin user management (all tenants)
            Route::apiResource('users', UserController::class);
        });

        Route::middleware(['auth:sanctum', 'active', 'role:super_admin'])
            ->prefix('v1/admin')
            ->group(function () {

                // ── Analytics ──────────────────────────────────────
                Route::get('overview', [SuperAdminController::class, 'overview']);
                Route::get('analytics/companies', [SuperAdminController::class, 'companyBreakdown']);
                Route::get('analytics/revenue-trend', [SuperAdminController::class, 'revenueTrend']);
                Route::get('analytics/top-menu-items', [SuperAdminController::class, 'topMenuItems']);

                // ── Impersonation ───────────────────────────────────
                Route::post('impersonate/{user}', [SuperAdminController::class, 'impersonate']);
                Route::delete('impersonate', [SuperAdminController::class, 'leaveImpersonation']);

                // ── Feature Flags ───────────────────────────────────
                Route::get('companies/{company}/flags', [SuperAdminController::class, 'featureFlags']);
                Route::patch('companies/{company}/flags/{key}', [SuperAdminController::class, 'toggleFlag']);
                Route::put('companies/{company}/flags', [SuperAdminController::class, 'bulkUpdateFlags']);

                // ── Audit Logs ──────────────────────────────────────
                Route::get('audit-logs', [SuperAdminController::class, 'auditLogs']);
                Route::get('audit-logs/recent', [SuperAdminController::class, 'recentActions']);
            });

        // ── Company Admin ──────────────────────────────────
        Route::middleware('role:company_admin')->group(function () {
            Route::get('user/list', [UserController::class, 'index'])->name('users.index.admin'); // DONE: All user list of company admin
            Route::post('user/store', [UserController::class, 'store'])->name('users.store.admin'); // DONE: Create new user
            Route::get('user/{user}/view', [UserController::class, 'show'])->name('users.show.admin');
            Route::post('user/{user}/update', [UserController::class, 'update'])->name('users.update.admin'); // DONE: Update user

            // Optional: support PATCH update too
            Route::patch('user/{user}', [UserController::class, 'update']);

            // Delete user
            Route::delete('user/{user}/delete', [UserController::class, 'destroy'])->name('users.destroy.admin');

            // Toggle active/inactive
            Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active.admin');


            /*
            |--------------------------------------------------------------------------
            | Teams Routes
            |--------------------------------------------------------------------------
            */

            // Get all teams
            Route::get('teams', [TeamController::class, 'index'])->name('teams.index');

            // Store new team
            Route::post('teams', [TeamController::class, 'store'])->name('teams.store');

            // Show single team
            Route::get('teams/{team}', [TeamController::class, 'show'])->name('teams.show');

            // Update team
            Route::put('teams/{team}', [TeamController::class, 'update'])->name('teams.update');

            // Optional PATCH support
            Route::patch('teams/{team}', [TeamController::class, 'update']);

            // Delete team
            Route::delete('teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

            // Assign user to team
            Route::post('teams/{team}/assign-user', [TeamController::class, 'assignUser'])->name('teams.assign-user');
        });

        // ── Company Admin ──────────────────────────────────────────
        Route::middleware('role:company_admin,super_admin')->group(function () {

            // Menu items
            Route::apiResource('menu-items', MenuItemController::class);
            Route::patch('menu-items/{menuItem}/toggle-availability', [MenuItemController::class, 'toggleAvailability']);

            // Order sessions
            Route::get('order-sessions', [OrderSessionController::class, 'index']);
            Route::post('order-sessions', [OrderSessionController::class, 'open']);
            Route::get('order-sessions/{id}', [OrderSessionController::class, 'show']);
            Route::patch('order-sessions/{id}/close', [OrderSessionController::class, 'close']);

            // Admin order management
            Route::get('order-sessions/{session}/orders', [OrderController::class, 'sessionOrders']);
            Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus']);

            // Single order payment
            Route::post('payments', [PaymentController::class, 'store']);

            // Bulk pay all unpaid orders in a session
            Route::post('order-sessions/{session}/bulk-pay', [PaymentController::class, 'bulkRecord']);

            // Refund
            Route::patch('payments/{payment}/refund', [PaymentController::class, 'refund']);

            // Session financial summary
            Route::get('order-sessions/{session}/summary', [PaymentController::class, 'sessionSummary']);

            // Payments for a specific order
            Route::get('orders/{order}/payments', [PaymentController::class, 'forOrder']);

            // Company-wide payment history
            Route::get('payments/history', [PaymentController::class, 'history'])->middleware('feature:analytics');
        });

        // ── Employee + above ───────────────────────────────────────
        Route::middleware('role:employee,company_admin,super_admin')->group(function () {

            // Browse menu
            Route::get('menu-items', [MenuItemController::class, 'index']);
            Route::get('menu-items/{id}', [MenuItemController::class, 'show']);

            // Active session check
            Route::get('order-sessions/active', [OrderSessionController::class, 'active']);

            // Place / manage own orders
            Route::post('orders', [OrderController::class, 'store']);
            Route::get('orders/my', [OrderController::class, 'myOrders']);
            Route::put('orders/{order}', [OrderController::class, 'update']);
            Route::patch('orders/{order}/cancel', [OrderController::class, 'cancel']);
        });
    });
});

// Fallback route
Route::fallback(function () {
    return response()->json([
        'status' => false,
        'message' => 'API endpoint not found.',
        'data' => null,
    ], 404);
});
