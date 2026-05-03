<?php

namespace App\Services\Analytics;

use App\Models\Company;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Platform-wide KPI snapshot.
     */
    public function platformOverview(): array
    {
        $companies   = Company::withoutGlobalScopes()->count();
        $activeComps = Company::withoutGlobalScopes()->where('status', 'active')->count();
        $users        = User::withoutGlobalScopes()->where('role', '!=', 'super_admin')->count();
        $orders       = Order::withoutGlobalScopes()->count();
        $revenue      = Payment::withoutGlobalScopes()->where('status', 'completed')->sum('amount');

        $ordersToday  = Order::withoutGlobalScopes()
            ->whereDate('created_at', today())
            ->count();

        $revenueToday = Payment::withoutGlobalScopes()
            ->where('status', 'completed')
            ->whereDate('created_at', today())
            ->sum('amount');

        return [
            'companies'       => $companies,
            'active_companies' => $activeComps,
            'total_users'     => $users,
            'total_orders'    => $orders,
            'total_revenue'   => round((float) $revenue, 2),
            'orders_today'    => $ordersToday,
            'revenue_today'   => round((float) $revenueToday, 2),
        ];
    }

    /**
     * Per-company breakdown — sortable.
     */
    public function companyBreakdown(string $sortBy = 'revenue'): array
    {
        $companies = Company::withoutGlobalScopes()
            ->withCount('users')
            ->with([
                'orders'   => fn($q) => $q->withoutGlobalScopes()->selectRaw('company_id, COUNT(*) as count, SUM(total) as total')->groupBy('company_id'),
                'payments' => fn($q) => $q->withoutGlobalScopes()->where('status', 'completed')->selectRaw('company_id, SUM(amount) as collected')->groupBy('company_id'),
            ])
            ->get()
            ->map(function ($company) {
                $ordersRow   = $company->orders->first();
                $paymentsRow = $company->payments->first();

                return [
                    'id'           => $company->id,
                    'name'         => $company->name,
                    'status'       => $company->status,
                    'plan'         => $company->plan,
                    'users_count'  => $company->users_count,
                    'orders_count' => $ordersRow ? (int) $ordersRow->count : 0,
                    'revenue'      => $paymentsRow ? round((float) $paymentsRow->collected, 2) : 0,
                ];
            });

        return match ($sortBy) {
            'revenue'      => $companies->sortByDesc('revenue')->values()->all(),
            'orders'       => $companies->sortByDesc('orders_count')->values()->all(),
            'users'        => $companies->sortByDesc('users_count')->values()->all(),
            default        => $companies->all(),
        };
    }

    /**
     * Revenue trend — last N days platform-wide.
     */
    public function revenueTrend(int $days = 30): array
    {
        return Payment::withoutGlobalScopes()
            ->where('status', 'completed')
            ->where('created_at', '>=', now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, SUM(amount) as revenue, COUNT(*) as transactions')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->map(fn($row) => [
                'date'         => $row->date,
                'revenue'      => round((float) $row->revenue, 2),
                'transactions' => (int) $row->transactions,
            ])
            ->all();
    }

    /**
     * Top menu items platform-wide.
     */
    public function topMenuItems(int $limit = 10): array
    {
        return DB::table('order_items')
            ->join('menu_items', 'order_items.menu_item_id', '=', 'menu_items.id')
            ->selectRaw('menu_items.name, menu_items.company_id, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as revenue')
            ->groupBy('menu_items.id', 'menu_items.name', 'menu_items.company_id')
            ->orderByDesc('qty')
            ->limit($limit)
            ->get()
            ->map(fn($row) => [
                'item'       => $row->name,
                'company_id' => $row->company_id,
                'qty_ordered' => (int) $row->qty,
                'revenue'    => round((float) $row->revenue, 2),
            ])
            ->all();
    }
}
