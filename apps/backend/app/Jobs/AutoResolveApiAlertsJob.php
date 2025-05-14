<?php

namespace App\Jobs;

use App\Enums\AlertRuleType;
use App\Interfaces\Messageable;
use App\Models\AlertInstance;
use App\Models\AlertRule;
use App\Models\ElasticCheck;
use App\Models\HealthCheck;
use App\Models\Log;
use App\Services\AlertRuleService;
use App\Services\ApiService;
use App\Services\ElasticService;
use App\Services\PrometheusInstanceService;
use App\Services\SendNotifyService;
use App\Helpers\Constants;
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
        $alerts = AlertRuleService::GetAlerts(AlertRuleType::API);
        $now = time();
        if ($alerts->isNotEmpty()) {
            foreach ($alerts as $alert) {
                if (!$alert['enableAutoResolve']) continue;
                $minutes = $alert->autoResolveMinutes;
                $instances = $alert->apiInstances();
                if (!empty($instances) && $instances->isNotEmpty()) {
                    $fireInstances = $instances->where("state", AlertInstance::FIRE);
                    foreach ($fireInstances as $instance) {
                        $lastUpdate = $instance->updatedAt->getTimeStamp();
                        if (($lastUpdate + ($minutes * 60)) < $now) {
                            $post = [
                                "apiToken" => $alert->apiToken,
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
