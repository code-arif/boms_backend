<?php

namespace App\Providers;

use App\Repositories\CompanyRepository;
use App\Services\CompanyService;
use App\Services\OrderService;
use App\Services\OrderSessionService;
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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
