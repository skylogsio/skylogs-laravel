<?php

namespace App\Providers;

use App\Enums\ClusterType;
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

        $clusterType = ClusterType::tryFrom(config("app.clusterType"));
        $sourceUrl = config('app.sourceUrl');
        $sourceToken = config('app.sourceToken');

        if ($clusterType === ClusterType::AGENT && (empty($sourceUrl) || empty($sourceToken))) {
            abort(500, 'SOURCE_URL and SOURCE_TOKEN must be set when CLUSTER_TYPE is "agent".');
        }
    }
}
