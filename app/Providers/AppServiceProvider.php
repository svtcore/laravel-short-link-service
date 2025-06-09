<?php

namespace App\Providers;

use App\Http\Services\DomainService;
use App\Http\Services\LinkService;
use App\Http\Services\UserService;
use Illuminate\Support\ServiceProvider;
use App\Http\Contracts\Interfaces\LinkServiceInterface;
use App\Http\Contracts\Interfaces\LinkHistoryServiceInterface;
use App\Http\Services\LinkHistoryService;
use App\Http\Contracts\Interfaces\DomainServiceInterface;
use App\Http\Contracts\Interfaces\UserServiceInterface;
use App\Http\Contracts\Interfaces\AdminStatisticsServiceInterface;
use App\Http\Services\AdminStatisticsService;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LinkServiceInterface::class, LinkService::class);
        $this->app->bind(LinkHistoryServiceInterface::class, LinkHistoryService::class);
        $this->app->bind(UserServiceInterface::class, UserService::class);
        $this->app->bind(DomainServiceInterface::class, DomainService::class);
        $this->app->bind(AdminStatisticsServiceInterface::class, AdminStatisticsService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
