<?php

namespace App\Jobs;

use App\Interfaces\Messageable;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ElasticCheck;
use App\Models\HealthCheck;
use App\Models\Log;
use App\Services\ApiService;
use App\Services\ElasticService;
use App\Services\PrometheusInstanceService;
use App\Services\SendNotifyService;
use App\Utility\Constants;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use function Symfony\Component\Translation\t;

class AutoResolveApiAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        $alerts = AlertRule::where("type", Constants::API)
            ->where("enableAutoResolve", true)
            ->get();
        $now = time();
        if ($alerts->isNotEmpty()) {
            foreach ($alerts as $alert) {
                $minutes = $alert->autoResolveMinutes;
                $instances = $alert->apiInstances();
                if (!empty($instances) && $instances->isNotEmpty()) {
                    $fireInstances = $instances->where("state", AlertInstance::FIRE);
                    foreach ($fireInstances as $instance) {
                        $lastUpdate = $instance->updated_at->getTimeStamp();
                        if (($lastUpdate + ($minutes * 60)) < $now) {
                            $post = [
                                "alertname" => $instance->alertname,
                                "instance" => $instance->instance,
                                "description" => "Alert Resolved Automatically"
                            ];
                            ApiService::ResolveAlert($post,);
                        }
                    }
                }

            }

        }

    }

}
