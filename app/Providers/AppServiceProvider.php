<?php

namespace App\Providers;

use App\Http\Services\AuthService;
use App\Http\Services\CategoryPlanService;
use App\Http\Services\CategoryServices;
use App\Http\Services\EventServices;
use App\Http\Services\GoalAdditionService;
use App\Http\Services\GoalService;
use App\Http\Services\MonthPlanService;
use App\Http\Services\NotificationService;
use App\Http\Services\TransactionServices;
use App\Http\Services\UserServices;
use App\Http\Services\EventService;
use App\Http\Services\WalletServices;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(AuthService::class);
        $this->app->bind(UserServices::class);
        $this->app->bind(WalletServices::class);
        $this->app->bind(CategoryServices::class);
        $this->app->bind(MonthPlanService::class);
        $this->app->bind(CategoryPlanService::class);
        $this->app->bind(TransactionServices::class);
        $this->app->bind(GoalService::class);
        $this->app->bind(GoalAdditionService::class);
        $this->app->bind(EventService::class);
        $this->app->bind(NotificationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
