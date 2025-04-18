<?php

namespace App\Jobs;

use App\Interfaces\Messageable;
use App\Models\AlertRule;
use App\Models\Log;
use App\Services\PrometheusInstanceService;
use App\Services\SendNotifyService;
use App\Helpers\Constants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;


class AddChecksJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $alertRules = AlertRule::whereIn('type', [
            Constants::ELASTIC,
            Constants::HEALTH,
            Constants::SERVICE,
        ])->get();

        foreach ($alertRules as $alert) {
            switch ($alert->type) {
                case Constants::ELASTIC:
                    CheckElasticJob::dispatch($alert);
                    break;
                case Constants::HEALTH:
                    CheckHealthJob::dispatch($alert);
                    break;
                case Constants::SERVICE:
                    CheckServiceJob::dispatch($alert);
                    break;
            }
        }

    }

}
