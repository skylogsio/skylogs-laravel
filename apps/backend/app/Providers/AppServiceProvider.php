<?php

namespace App\Providers;

use App\Models\AlertRule;
use App\Models\DataSource\DataSource;
use App\Models\Endpoint;
use App\Observers\AlertRuleObserver;
use App\Observers\DataSourceObserver;
use App\Observers\EndpointObserver;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

    }
}
