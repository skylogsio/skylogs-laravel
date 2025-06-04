<?php

namespace App\Providers;

use App\Models\AlertRule;
use App\Models\DataSource\DataSource;
use App\Models\Endpoint;
use App\Observers\AlertRuleObserver;
use App\Observers\DataSourceObserver;
use App\Observers\EndpointObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\ServiceProvider;
use RateLimiter;

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

//        RateLimiter::for('apiWebhook', function (Request $request) {
//            return Limit::perMinute(100)->by($request->bearerToken() ?: $request->ip());
//        });
    }
}
