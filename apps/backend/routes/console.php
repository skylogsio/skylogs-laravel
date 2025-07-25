<?php

use App\Enums\AlertRuleType;
use App\Enums\ClusterType;
use App\Jobs\AddChecksJob;
use App\Jobs\AutoResolveApiAlertsJob;
use App\Jobs\CheckPrometheusJob;
use App\Jobs\SyncCluster;
use App\Models\AlertRule;
use App\Models\DataSource\DataSource;
use App\Models\User;
use App\Services\ClusterService;


Artisan::command('app:test', function () {
    if (config('app.env') === 'local') {

    }
})->purpose('Run Code');

Artisan::command('app:sync-data', function () {
    if (app(ClusterService::class)->type() == ClusterType::AGENT) {
        SyncCluster::dispatch();
    }else{

    }
})->purpose('Sync Data With Main Cluster');

Schedule::call(function () {
    if (app(ClusterService::class)->type() == ClusterType::AGENT) {
        SyncCluster::dispatch();
    }
})->everyTenMinutes();

Schedule::job(new CheckPrometheusJob)->everyFiveSeconds();
Schedule::job(new AddChecksJob)->everyFiveSeconds();
Schedule::job(new AutoResolveApiAlertsJob)->everyFiveSeconds();

