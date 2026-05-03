<?php

namespace App\Providers;

use App\Repositories\CompanyRepository;
use App\Repositories\Payment\PaymentRepository;
use App\Services\Analytics\AnalyticsService;
use App\Services\Audit\AuditLogService;
use App\Services\CompanyService;
use App\Services\FeatureFlagService;
use App\Services\Impersonation\ImpersonationService;
use App\Services\OrderService;
use App\Services\OrderSessionService;
use App\Services\Payment\PaymentService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(CompanyRepository::class, CompanyRepository::class);
        $this->app->bind(CompanyService::class, CompanyService::class);
        $this->app->bind(UserService::class, UserService::class);
        $this->app->bind(OrderSessionService::class, OrderSessionService::class);
        $this->app->bind(OrderService::class, OrderService::class);
        $this->app->bind(PaymentRepository::class, PaymentRepository::class);
        $this->app->bind(PaymentService::class, PaymentService::class);
        $this->app->bind(AnalyticsService::class, AnalyticsService::class);
        $this->app->bind(ImpersonationService::class, ImpersonationService::class);
        $this->app->bind(FeatureFlagService::class, FeatureFlagService::class);
        $this->app->bind(AuditLogService::class, AuditLogService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
