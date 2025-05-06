<?php

use App\Jobs\AddChecksJob;
use App\Jobs\AutoResolveApiAlertsJob;
use App\Jobs\CheckPrometheusJob;


Artisan::command('app:test', function () {
    $this->comment("Test job");
     CheckPrometheusJob::dispatch();
})->purpose('Run Code');

if (config('app.env') === 'production') {
    Schedule::job(new CheckPrometheusJob)->everyFiveSeconds();
    Schedule::job(new AddChecksJob)->everyFiveSeconds();
    Schedule::job(new AutoResolveApiAlertsJob)->everyFiveSeconds();
}else{
    Schedule::job(new CheckPrometheusJob)->everyThirtySeconds();
    Schedule::job(new AddChecksJob)->everyThirtySeconds();
}
