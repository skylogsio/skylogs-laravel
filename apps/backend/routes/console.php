<?php

use App\Enums\AlertRuleType;
use App\Jobs\AddChecksJob;
use App\Jobs\AutoResolveApiAlertsJob;
use App\Jobs\CheckPrometheusJob;
use App\Models\AlertRule;


Artisan::command('app:test', function () {
    if (config('app.env') === 'local') {

    }
})->purpose('Run Code');

if (config('app.env') === 'production') {
    Schedule::job(new CheckPrometheusJob)->everyFiveSeconds();
    Schedule::job(new AddChecksJob)->everyFiveSeconds();
    Schedule::job(new AutoResolveApiAlertsJob)->everyFiveSeconds();
} else {
    Schedule::job(new CheckPrometheusJob)->everyThirtySeconds();
    Schedule::job(new AddChecksJob)->everyThirtySeconds();
}
