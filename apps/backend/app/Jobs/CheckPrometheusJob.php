<?php

namespace App\Jobs;

use App\interfaces\Messageable;
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

class CheckPrometheusJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->onQueue('httpRequests');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        echo "TEST";

        $alertRules = AlertRule::where('type', Constants::PROMETHEUS)->get();
        $alerts = PrometheusInstanceService::getTriggered();
//        $prometheusChecks = PrometheusCheck::get();

        // check new alerts.
        $fireAlertsByRule = PrometheusService::CheckPrometheusFiredAlerts($alerts,$alertRules);
        PrometheusService::CheckAlerts($alertRules,$fireAlertsByRule);

        // send notify if an alert is new
//        PrometheusService::SendNotifyIfAlertNew($fireAlertsByRule);

        //check resolved alerts
//        PrometheusService::CheckResolvedAlerts($prometheusChecks,$alerts);

    }

}
