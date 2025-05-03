<?php

namespace App\Providers;

use App\Models\DataSource\DataSource;
use App\Observers\DataSourceObserver;
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
        DataSource::observe(DataSourceObserver::class);
    }
}
