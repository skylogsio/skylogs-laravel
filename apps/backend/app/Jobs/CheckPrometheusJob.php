<?php

namespace App\Jobs;

use App\Enums\AlertRuleType;
use App\Models\AlertRule;
use App\Services\PrometheusInstanceService;
use App\Services\PrometheusService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckPrometheusJob implements ShouldQueue,ShouldBeUnique
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

        $alertRules = AlertRule::where('type', AlertRuleType::PROMETHEUS)->get();
//        $alerts = PrometheusInstanceService::getTriggered();
        $alerts = cache()->tags(['prometheus','triggered'])->remember("prometheusTriggered",3600, function () {
            return PrometheusInstanceService::GetTriggered();
        });
        ds($alerts)->label("all Alerts");
//        $alerts = collect($alerts)->filter(function ($alertRule) {return $alertRule['labels']['alertname'] == "KubeAPIDown";})->toArray();
//        ds($alerts)->label("alerts");
        $fireAlertsByRule = PrometheusService::CheckPrometheusFiredAlerts($alerts,$alertRules);
        ds($fireAlertsByRule)->label("fireAlertsByRule");
        PrometheusService::CheckAlerts($alertRules,$fireAlertsByRule);


    }

}
