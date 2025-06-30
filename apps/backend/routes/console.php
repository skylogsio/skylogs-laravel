<?php

use App\Enums\AlertRuleType;
use App\Jobs\AddChecksJob;
use App\Jobs\AutoResolveApiAlertsJob;
use App\Jobs\CheckPrometheusJob;
use App\Models\AlertRule;
use App\Models\DataSource\DataSource;


Artisan::command('app:test', function () {
    if (config('app.env') === 'local') {

    }
})->purpose('Run Code');


Schedule::job(new CheckPrometheusJob)->everyFiveSeconds();
Schedule::job(new AddChecksJob)->everyFiveSeconds();
Schedule::job(new AutoResolveApiAlertsJob)->everyFiveSeconds();

