<?php

namespace App\Jobs;

use App\Interfaces\Messageable;
use App\Models\AlertRule;
use App\Models\Log;
use App\Models\PrometheusCheck;
use App\Services\PrometheusInstanceService;
use App\Services\PrometheusService;
use App\Services\SendNotifyService;
use App\Utility\Constants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use function Symfony\Component\Translation\t;

class RefreshPrometheusCheckJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('clean');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        PrometheusService::CleanChecks();

    }

}
