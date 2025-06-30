<?php

namespace App\Jobs;

use App\Interfaces\Messageable;
use App\Models\AlertRule;
use App\Models\HealthCheck;
use App\Models\HealthHistory;
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
use function PHPUnit\Framework\throwException;
use function Symfony\Component\Translation\t;

class CheckHealthJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $alert;

    public function __construct($alert)
    {
        $this->onQueue('httpRequests');
        $this->alert = $alert;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
//        echo "TEST";
        $check = HealthCheck::firstOrCreate(
            [
                "alertRuleId" => $this->alert->_id
            ],
            [
                "url" => $this->alert->url,
                "threshold_down" => $this->alert->threshold_down,
                "threshold_up" => $this->alert->threshold_up,
                "basic_auth_username" => $this->alert->basic_auth_username,
                "basic_auth_password" => $this->alert->basic_auth_password,
                "state" => HealthCheck::UP,
                "counter" => 0
            ]
        );
        try {

            $pendingRequest = \Http::timeout(5);

            if(!empty($check->basic_auth_username) && !empty($check->basic_auth_password)){
                $pendingRequest = $pendingRequest->withBasicAuth($check->basic_auth_username, $check->basic_auth_password);
            }

            $response = $pendingRequest->get($check->url);
            $check->refresh();
            if ($response->status() == 200 || $response->status() == 201) {

                if ($check->counter != 0) {
                    $check->counter -= 1;
                    if ($check->counter < $check->threshold_up && $check->state == HealthCheck::DOWN) {

                        $check->state = HealthCheck::UP;
                        $check->notifyAt = time();
                        $check->save();
                        SendNotifyService::CreateNotify(SendNotifyJob::HEALTH_CHECK, $check,$this->alert->_id);
                        HealthHistory::create(
                            [
                                "alertRuleId" => $this->alert->_id,
                                "alertRuleName" => $this->alert->alertname,
                                "url" => $this->alert->url,
                                "threshold_down" => $this->alert->threshold_down,
                                "threshold_up" => $this->alert->threshold_up,
                                "state" => HealthCheck::UP,
                                "counter" => 0
                            ]
                        );


                    } else {
                        $check->save();
                    }
                }

            } else {
                throw new \Exception("Not Ok");
            }

        } catch (\Exception $e) {


            if ($check->counter != $check->threshold_down) {
                if ($check->counter < $check->threshold_down)
                    $check->counter += 1;
                if ($check->counter >= $check->threshold_down && $check->state == HealthCheck::UP) {
                    $check->state = HealthCheck::DOWN;
                    $check->notifyAt = time();
                    $check->save();
                    SendNotifyService::CreateNotify(SendNotifyJob::HEALTH_CHECK, $check,$this->alert->_id);

                    HealthHistory::create(
                        [
                            "alertRuleId" => $this->alert->_id,
                            "alertRuleName" => $this->alert->alertname,
                            "url" => $this->alert->url,
                            "threshold_down" => $this->alert->threshold_down,
                            "threshold_up" => $this->alert->threshold_up,
                            "state" => HealthCheck::DOWN,
                            "counter" => $check->counter
                        ]
                    );


                } else {
                    $check->save();
                }
            }
        }

    }

    public function uniqueId()
    {
        return $this->alert->_id;
    }

}
