<?php

namespace App\Providers;

use App\Repositories\Api\BaseRepository;
use App\Repositories\Api\Contracts\BaseRepositoryInterface;
use App\Repositories\Dashboard\Contracts\DashboardBaseRepositoryInterface;
use App\Repositories\Dashboard\DashboardBaseRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(BaseRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(DashboardBaseRepositoryInterface::class, DashboardBaseRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
